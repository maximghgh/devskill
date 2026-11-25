<?php

namespace Tests\Feature;

use App\Mail\CourseIssuedMail;
use App\Models\Course;
use App\Models\CourseApplication;
use App\Models\Group;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GroupEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Course $course;
    private int $topicId;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = $this->user('admin', 3);
        $this->course = Course::create([
            'card_title' => 'Курс', 'course_name' => 'Курс', 'price' => 1000,
            'difficulty' => 'basic', 'language' => '[]',
        ]);
        $this->topicId = DB::table('topics')->insertGetId([
            'course_id' => $this->course->id, 'title' => 'Тема', 'order' => 1,
        ]);
        $this->actingAs($this->admin);
    }

    private function user(string $login, int $role = 1, bool $withEmail = true): User
    {
        return User::create([
            'name' => $login, 'login' => $login, 'role' => $role,
            'email' => $withEmail ? $login . '@example.com' : null,
            'password' => bcrypt('password'),
        ]);
    }

    private function url(?Group $group = null): string
    {
        return '/api/admin/course/' . $this->course->id . '/groups' . ($group ? '/' . $group->id : '');
    }

    private function group(array $ids = []): Group
    {
        $group = Group::create(['course_id' => $this->course->id, 'name_group' => 'Группа', 'students_count' => count($ids)]);
        $group->students()->sync($ids);
        $group->topics()->sync([$this->topicId]);
        return $group;
    }

    private function application(User $student, string $status = 'awaiting_signing'): CourseApplication
    {
        return CourseApplication::create([
            'course_id' => $this->course->id, 'user_id' => $student->id,
            'full_name' => $student->name, 'phone' => '+79991234567',
            'email' => $student->email ?? '', 'status' => $status, 'admin_comment' => 'Не терять',
        ]);
    }

    public function test_bulk_creation_issues_courses_and_reuses_linked_and_guest_applications(): void
    {
        $first = $this->user('first');
        $second = $this->user('second');
        $third = $this->user('third', 1, false);
        $linked = $this->application($first);
        $guest = $this->application($second, 'awaiting_payment');
        $guest->update(['user_id' => null]);
        $response = $this->postJson($this->url(), [
            'name_group' => 'Набор', 'student_ids' => [$first->id, $second->id, $third->id],
        ])->assertCreated()->assertJsonPath('group.students_count', 3)
            ->assertJsonPath('group.topic_ids', [$this->topicId]);
        $this->assertDatabaseCount('course_applications', 3);
        $this->assertDatabaseCount('purchases', 3);
        $this->assertDatabaseCount('group_students', 3);
        foreach ([$first, $second, $third] as $student) {
            $this->assertDatabaseHas('course_applications', ['user_id' => $student->id, 'status' => 'issued']);
            $this->assertDatabaseHas('purchases', ['user_id' => $student->id, 'status' => 'completed']);
        }
        $this->assertSame('Не терять', $linked->fresh()->admin_comment);
        $this->assertSame($second->id, $guest->fresh()->user_id);
        $this->assertSame('+79991234567', $guest->fresh()->phone);
        Mail::assertSent(CourseIssuedMail::class, 2);
        $this->assertSame(3, Group::find($response->json('group.id'))->students()->count());
    }

    public function test_resaving_group_does_not_duplicate_access_applications_or_emails(): void
    {
        $student = $this->user('student');
        $response = $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertCreated();
        $group = Group::findOrFail($response->json('group.id'));
        $this->patchJson($this->url($group), ['name_group' => 'Новое имя', 'student_ids' => [$student->id]])->assertOk();
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('course_applications', 1);
        $this->assertDatabaseCount('group_students', 1);
        Mail::assertSent(CourseIssuedMail::class, 1);
        $this->patchJson($this->url($group), ['name_group' => 'Только переименование'])->assertOk();
        $this->assertSame(1, $group->fresh()->students_count);
        $this->assertSame([$this->topicId], $group->topics()->pluck('topics.id')->all());
    }

    public function test_existing_access_receipt_and_other_courses_are_preserved(): void
    {
        $student = $this->user('student');
        $purchase = Purchase::create([
            'user_id' => $student->id, 'course_id' => $this->course->id,
            'payment_method' => 'card', 'status' => 'completed', 'receipt_path' => 'receipts/keep.pdf',
            'payment_details' => '{"keep":true}',
        ]);
        $application = $this->application($student, 'issued');
        $other = $this->course->replicate();
        $other->slug = 'other';
        $other->save();
        $otherPurchase = Purchase::create(['user_id' => $student->id, 'course_id' => $other->id, 'payment_method' => 'contract', 'status' => 'completed']);
        $purchase->refresh();
        $otherPurchase->refresh();
        $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertCreated();
        $this->assertEquals($purchase->getAttributes(), $purchase->fresh()->getAttributes());
        $this->assertEquals($otherPurchase->getAttributes(), $otherPurchase->fresh()->getAttributes());
        $this->assertSame('issued', $application->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_close_reopen_and_removal_keep_progress_scores_and_group_topics(): void
    {
        $student = $this->user('student');
        $other = $this->user('other');
        $response = $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertCreated();
        $group = Group::findOrFail($response->json('group.id'));
        $chapter = DB::table('chapters')->insertGetId(['topic_id' => $this->topicId, 'title' => 'Урок', 'type' => 'text']);
        DB::table('user_chapter_progress')->insert(['user_id' => $student->id, 'chapter_id' => $chapter, 'completed_at' => now()]);
        DB::table('lesson_scores')->insert(['user_id' => $student->id, 'chapter_id' => $chapter, 'score' => 90]);
        $application = CourseApplication::firstOrFail();
        $url = '/api/course-applications/' . $application->id;
        $this->patchJson($url, ['status' => 'closed'])->assertOk();
        $this->assertDatabaseCount('purchases', 0);
        $this->patchJson($this->url($group), ['name_group' => 'Группа 2', 'student_ids' => [$student->id, $other->id]])->assertOk();
        $this->assertDatabaseMissing('purchases', ['user_id' => $student->id]);
        $this->assertSame('closed', $application->fresh()->status);
        $this->patchJson($url, ['status' => 'issued'])->assertOk();
        $this->assertDatabaseHas('purchases', ['user_id' => $student->id]);
        $this->patchJson($this->url($group), ['name_group' => 'Группа 2', 'student_ids' => [$other->id]])->assertOk();
        $this->assertDatabaseHas('purchases', ['user_id' => $student->id]);
        $this->assertDatabaseHas('user_chapter_progress', ['user_id' => $student->id, 'chapter_id' => $chapter]);
        $this->assertDatabaseHas('lesson_scores', ['user_id' => $student->id, 'score' => 90]);
        $this->assertSame([$this->topicId], $group->topics()->pluck('topics.id')->all());
        $this->assertSame(1, $group->fresh()->students_count);
        $this->patchJson($this->url($group), [
            'name_group' => 'Пустая группа', 'student_ids' => [],
            'expected_student_ids' => [$other->id],
        ])->assertOk()->assertJsonPath('group.students_count', 0);
        $this->assertSame(0, $group->students()->count());
        $this->assertDatabaseHas('purchases', ['user_id' => $other->id]);
        $this->assertDatabaseHas('lesson_scores', ['user_id' => $student->id, 'score' => 90]);
        $this->assertSame([$this->topicId], $group->topics()->pluck('topics.id')->all());
    }

    public function test_adding_closed_student_reopens_existing_application(): void
    {
        $student = $this->user('student');
        $application = $this->application($student, 'closed');
        $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertCreated();
        $this->assertSame('issued', $application->fresh()->status);
        $this->assertDatabaseCount('course_applications', 1);
        $this->assertDatabaseCount('purchases', 1);
    }

    public function test_invalid_selection_and_wrong_course_do_not_change_any_data(): void
    {
        $student = $this->user('student');
        $group = $this->group([$student->id]);
        foreach ([[$student->id, $student->id], [$student->id, 999999], [$this->admin->id]] as $ids) {
            $this->patchJson($this->url($group), ['name_group' => 'Нельзя сохранить', 'student_ids' => $ids])->assertUnprocessable();
        }
        $this->assertSame('Группа', $group->fresh()->name_group);
        $this->assertSame([$student->id], $group->students()->pluck('users.id')->all());
        $other = $this->course->replicate();
        $other->slug = 'other';
        $other->save();
        $this->patchJson('/api/admin/course/' . $other->id . '/groups/' . $group->id, [
            'name_group' => 'Нельзя сохранить', 'student_ids' => [],
        ])->assertNotFound();
        $this->assertDatabaseCount('course_applications', 0);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_students_in_another_group_are_not_silently_moved(): void
    {
        $student = $this->user('student');
        $other = $this->user('other');
        $existing = $this->group([$student->id]);
        $this->postJson($this->url(), ['name_group' => 'Другая', 'student_ids' => [$other->id, $student->id]])->assertUnprocessable();
        $this->assertDatabaseCount('groups', 1);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('course_applications', 0);
        $this->assertSame([$student->id], $existing->students()->pluck('users.id')->all());
    }

    public function test_candidates_include_imported_students_access_and_membership_without_private_fields(): void
    {
        $first = $this->user('first');
        $second = $this->user('second', 1, false);
        $this->group([$first->id]);
        Purchase::create(['user_id' => $first->id, 'course_id' => $this->course->id, 'payment_method' => 'contract', 'status' => 'completed']);
        $this->user('teacher', 2);
        $response = $this->getJson('/api/admin/course/' . $this->course->id . '/group-students')->assertOk()->assertJsonCount(2);
        $response->assertJsonPath('0.login', 'first')->assertJsonPath('0.has_access', true)
            ->assertJsonPath('0.groups.0.name_group', 'Группа')->assertJsonPath('1.has_access', false)
            ->assertJsonPath('1.id', $second->id)->assertJsonMissingPath('0.password')
            ->assertJsonMissingPath('0.inn')->assertJsonMissingPath('0.parent_info');
    }

    public function test_only_admin_can_enroll_and_view_candidates(): void
    {
        $student = $this->user('student');
        $group = $this->group();
        foreach ([$student, $this->user('teacher', 2)] as $user) {
            $this->actingAs($user)->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertForbidden();
            $this->patchJson($this->url($group), ['name_group' => 'Другая', 'student_ids' => [$student->id]])->assertForbidden();
            $this->getJson('/api/admin/course/' . $this->course->id . '/group-students')->assertForbidden();
        }
        auth()->forgetGuards();
        $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertUnauthorized();
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_bulk_enrollment_of_150_imported_accounts_is_repeatable(): void
    {
        $ids = [];
        for ($i = 0; $i < 150; $i++) {
            $ids[] = $this->user('import' . $i, 1, false)->id;
        }
        $response = $this->postJson($this->url(), ['name_group' => 'Импорт', 'student_ids' => $ids])
            ->assertCreated()->assertJsonPath('group.students_count', 150);
        $group = Group::findOrFail($response->json('group.id'));
        $this->patchJson($this->url($group), ['name_group' => 'Импорт', 'student_ids' => $ids])->assertOk();
        foreach (['group_students', 'course_applications', 'purchases'] as $table) {
            $this->assertDatabaseCount($table, 150);
        }
        Mail::assertNothingSent();
    }

    public function test_mail_failure_does_not_undo_enrollment(): void
    {
        $student = $this->user('student');
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$student->id]])->assertCreated();
        $this->assertDatabaseHas('course_applications', ['user_id' => $student->id, 'status' => 'issued']);
        $this->assertDatabaseHas('purchases', ['user_id' => $student->id]);
        $this->assertDatabaseHas('group_students', ['user_id' => $student->id]);
    }

    public function test_stale_group_form_cannot_erase_recently_added_students(): void
    {
        $first = $this->user('first');
        $second = $this->user('second');
        $group = $this->group([$first->id]);
        $this->patchJson($this->url($group), [
            'name_group' => 'Группа', 'student_ids' => [$first->id, $second->id],
            'expected_student_ids' => [$first->id],
        ])->assertOk();
        $this->patchJson($this->url($group), [
            'name_group' => 'Старая форма', 'student_ids' => [$first->id],
            'expected_student_ids' => [$first->id],
        ])->assertUnprocessable();
        $this->assertSame('Группа', $group->fresh()->name_group);
        $this->assertSame(2, $group->fresh()->students_count);
        $this->assertDatabaseHas('group_students', ['group_id' => $group->id, 'user_id' => $second->id]);
    }

    public function test_failure_during_issuance_rolls_back_group_and_all_students(): void
    {
        $first = $this->user('first');
        $second = $this->user('second');
        $this->mock(\App\Services\CourseAccessService::class, function ($mock) {
            $calls = 0;
            $mock->shouldReceive('openCourseAccess')->twice()->andReturnUsing(function ($application) use (&$calls) {
                if (++$calls === 2) {
                    throw new \RuntimeException('Test failure');
                }
                (new \App\Services\CourseAccessService())->openCourseAccess($application);
            });
        });
        $this->postJson($this->url(), ['name_group' => 'Группа', 'student_ids' => [$first->id, $second->id]])->assertStatus(500);
        foreach (['groups', 'group_students', 'group_topics', 'course_applications', 'purchases'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        Mail::assertNothingSent();
    }
}
