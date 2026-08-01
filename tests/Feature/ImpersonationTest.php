<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Проверка входа администратора под аккаунтом пользователя.
 *
 * Работаем на реальной sqlite-базе внутри транзакции, которая
 * откатывается после каждого теста — данные проекта не меняются.
 */
class ImpersonationTest extends TestCase
{
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => base_path('database/database.sqlite'),
        ]);
        DB::purge('sqlite');

        DB::beginTransaction();

        $this->admin = User::create([
            'name' => 'Тест Админ',
            'login' => 'test.admin.' . uniqid(),
            'password' => Hash::make('secret123'),
            'role' => 3,
        ]);

        $this->student = User::create([
            'name' => 'Тест Ученик',
            'login' => 'test.student.' . uniqid(),
            'password' => Hash::make('secret123'),
            'role' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_admin_can_impersonate_user_and_return_back(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/impersonate/' . $this->student->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.id', $this->student->id)
            ->assertJsonPath('impersonator.id', $this->admin->id);

        // Сессия переключена на пользователя, метка администратора сохранена.
        $this->assertSame($this->student->id, Auth::id());
        $this->assertSame($this->admin->id, session('impersonator_id'));

        // Чувствительных полей в ответе быть не должно.
        $response->assertJsonMissingPath('user.inn');
        $response->assertJsonMissingPath('user.password');

        // Возврат в админку.
        $back = $this->postJson('/impersonate/stop');

        $back->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.id', $this->admin->id);

        $this->assertSame($this->admin->id, Auth::id());
        $this->assertNull(session('impersonator_id'));
    }

    public function test_status_endpoint_reports_impersonation(): void
    {
        $this->actingAs($this->admin)->postJson('/impersonate/' . $this->student->id);

        $this->getJson('/impersonate/status')
            ->assertOk()
            ->assertJsonPath('impersonating', true)
            ->assertJsonPath('user.id', $this->student->id)
            ->assertJsonPath('impersonator.id', $this->admin->id);
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $otherAdmin = User::create([
            'name' => 'Другой Админ',
            'login' => 'test.admin2.' . uniqid(),
            'password' => Hash::make('secret123'),
            'role' => 3,
        ]);

        $this->actingAs($this->admin)
            ->postJson('/impersonate/' . $otherAdmin->id)
            ->assertStatus(403);

        $this->assertSame($this->admin->id, Auth::id());
    }

    public function test_non_admin_cannot_impersonate(): void
    {
        $this->actingAs($this->student)
            ->postJson('/impersonate/' . $this->admin->id)
            ->assertStatus(403);
    }

    public function test_stop_without_impersonation_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/impersonate/stop')
            ->assertStatus(422);
    }

    public function test_guest_cannot_impersonate(): void
    {
        $this->postJson('/impersonate/' . $this->student->id)
            ->assertStatus(401);
    }
}
