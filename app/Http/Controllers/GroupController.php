<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Group;
use App\Models\CourseApplication;
use App\Models\Purchase;
use App\Models\User;
use App\Services\CourseAccessService;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function index(Course $course)
    {
        $groups = $course->groups()
            ->with(['topics:id', 'students:id'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Group $group) {
                $group = $this->withTopicIds($group);

                $group->setAttribute('student_ids', $group->students->pluck('id')->all());
                $group->unsetRelation('students');

                return $group;
            });

        return response()->json($groups);
    }

    public function show(Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $group->load(['students:id,name', 'topics:id']);

        return response()->json($this->withTopicIds($group));
    }

    public function students(Course $course)
    {
        $memberships = DB::table('group_students')
            ->join('groups', 'groups.id', '=', 'group_students.group_id')
            ->where('groups.course_id', $course->id)
            ->select('group_students.user_id', 'groups.id', 'groups.name_group')
            ->get()->groupBy('user_id');
        $accessIds = Purchase::where('course_id', $course->id)->pluck('user_id')->flip();

        $students = User::where(function ($query) use ($memberships) {
            $query->where('role', 1)->orWhereIn('id', $memberships->keys());
        })->orderBy('name')->orderBy('id')->get(['id', 'name', 'login', 'email'])
            ->map(function (User $student) use ($memberships, $accessIds) {
                $student->setAttribute('has_access', $accessIds->has($student->id));
                $student->setAttribute('groups', $memberships->get($student->id, collect())->values());
                return $student;
            });

        return response()->json($students);
    }

    public function store(Request $request, Course $course)
    {
        return $this->saveGroup($request, $course);
    }

    public function update(Request $request, Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        return $this->saveGroup($request, $course, $group);
    }

    private function saveGroup(Request $request, Course $course, ?Group $group = null)
    {
        $validated = $request->validate([
            'name_group' => 'required|string|max:255',
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'integer|distinct|exists:users,id',
            'expected_student_ids' => 'sometimes|array',
            'expected_student_ids.*' => 'integer|distinct',
            'topic_ids' => 'sometimes|array',
            'topic_ids.*' => 'integer',
        ]);

        $isNew = !$group;
        $notifications = [];
        $access = app(CourseAccessService::class);

        $group = DB::transaction(function () use ($course, $group, $validated, $access, &$notifications) {
            Course::whereKey($course->id)->lockForUpdate()->firstOrFail();
            if ($group) {
                $group = Group::whereKey($group->id)->lockForUpdate()->firstOrFail();
            }
            $previousIds = $group ? $group->students()->pluck('users.id')->all() : [];
            if ($group && array_key_exists('expected_student_ids', $validated)) {
                $expectedIds = array_map('intval', $validated['expected_student_ids']);
                $currentIds = array_map('intval', $previousIds);
                sort($expectedIds);
                sort($currentIds);
                if ($expectedIds !== $currentIds) {
                    throw ValidationException::withMessages([
                        'student_ids' => 'Состав группы уже изменился. Закройте и откройте окно группы заново, чтобы не потерять изменения.',
                    ]);
                }
            }
            $studentIds = array_map('intval', $validated['student_ids'] ?? $previousIds);
            $addedIds = array_values(array_diff($studentIds, $previousIds));
            $users = User::whereIn('id', $addedIds)->orderBy('id')->lockForUpdate()->get();

            if ($users->contains(fn (User $user) => (int) $user->role !== 1)) {
                throw ValidationException::withMessages(['student_ids' => 'Добавлять в группу можно только учеников.']);
            }

            $otherGroup = Group::where('course_id', $course->id)
                ->when($group, fn ($query) => $query->where('id', '!=', $group->id))
                ->whereHas('students', fn ($query) => $query->whereIn('users.id', $addedIds))
                ->first();
            if ($otherGroup) {
                throw ValidationException::withMessages([
                    'student_ids' => 'Один из выбранных учеников уже в группе «' . $otherGroup->name_group . '». Сначала измените состав этой группы.',
                ]);
            }

            $group ??= new Group(['course_id' => $course->id]);
            $group->name_group = $validated['name_group'];
            $group->students_count = count($studentIds);
            $group->save();
            $group->students()->sync($studentIds);

            if (array_key_exists('topic_ids', $validated)) {
                $group->topics()->sync($this->courseTopicIds($course, $validated['topic_ids']));
            } elseif ($group->wasRecentlyCreated) {
                $group->topics()->sync($this->firstTopicId($course));
            }

            foreach ($users as $user) {
                $application = CourseApplication::where('course_id', $course->id)
                    ->where('user_id', $user->id)
                    ->orderByDesc('id')->lockForUpdate()->first();
                if (!$application && $user->email) {
                    $application = CourseApplication::where('course_id', $course->id)
                        ->whereNull('user_id')->where('email', $user->email)
                        ->orderByDesc('id')->lockForUpdate()->first();
                }
                $application ??= new CourseApplication([
                    'course_id' => $course->id,
                    'full_name' => $user->name,
                    'phone' => $user->phone ?? '',
                    'email' => $user->email ?? '',
                ]);
                $alreadyIssued = $application->status === CourseApplication::STATUS_ISSUED;
                $application->user_id = $user->id;
                $application->status = CourseApplication::STATUS_ISSUED;
                $application->save();
                $access->openCourseAccess($application);
                if (!$alreadyIssued) {
                    $notifications[] = $application;
                }
            }

            return $group->load(['students:id,name', 'topics:id']);
        });

        if ($notifications) {
            app()->terminating(function () use (&$notifications) {
                $pending = $notifications;
                $notifications = [];
                $access = app(CourseAccessService::class);
                foreach ($pending as $application) {
                    $access->notifyCourseIssued($application);
                }
            });
        }

        return response()->json(['group' => $this->withTopicIds($group)], $isNew ? 201 : 200);
    }

    /**
     * Заменить набор тем, открытых группе.
     * Вызывается со страницы курса у преподавателя.
     */
    public function syncTopics(Request $request, Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $validated = $request->validate([
            'topic_ids' => 'present|array',
            'topic_ids.*' => 'integer',
        ]);

        $topicIds = $this->courseTopicIds($course, $validated['topic_ids']);
        $group->topics()->sync($topicIds);

        return response()->json([
            'success' => true,
            'group_id' => $group->id,
            'topic_ids' => $topicIds,
        ]);
    }

    public function destroy(Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $group->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Отдаём плоский список id тем вместо вложенных объектов —
     * фронту нужны только идентификаторы для переключателей.
     */
    private function withTopicIds(Group $group): Group
    {
        $group->setAttribute('topic_ids', $group->topics->pluck('id')->all());
        $group->unsetRelation('topics');

        return $group;
    }

    /**
     * Первая тема курса по порядку — стартовый набор новой группы.
     * Пустой массив, если тем на курсе ещё нет.
     */
    private function firstTopicId(Course $course): array
    {
        $id = Topic::where('course_id', $course->id)
            ->orderBy('order')
            ->orderBy('id')
            ->value('id');

        return $id ? [$id] : [];
    }

    /**
     * Отсеиваем темы, не принадлежащие курсу: id приходит с клиента.
     */
    private function courseTopicIds(Course $course, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return Topic::where('course_id', $course->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();
    }
}
