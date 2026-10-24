<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_complete_profile_and_log_out(): void
    {
        $this->post(route('register.store'), [
            'email' => 'student@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('login'));

        $student = User::query()->where('email', 'student@example.com')->firstOrFail();
        $this->assertFalse($student->profileIsComplete());
        $this->assertSame(User::ROLE_STUDENT, $student->role);

        $this->post(route('login.store'), [
            'email' => 'student@example.com',
            'password' => 'secure-password',
        ])->assertRedirect(route('continue'));

        $this->get(route('continue'))->assertRedirect(route('profile.setup'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $student->id,
            'event' => ActivityLog::EVENT_LOGIN,
        ]);

        $this->post(route('profile.setup.update'), [
            'first_name' => 'Taylor',
            'last_name' => 'Santos',
            'program' => 'BSIT',
            'year_level' => 2,
        ])->assertRedirect(route('student.dashboard'));

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Taylor Santos')
            ->assertSee('BSIT');

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $student->id,
            'event' => ActivityLog::EVENT_LOGOUT,
        ]);
    }

    public function test_admin_dashboard_is_only_available_to_administrators(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_student_activity_and_account_metrics(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $student = User::factory()->create([
            'name' => 'Taylor Santos',
            'email' => 'taylor@example.com',
            'role' => User::ROLE_STUDENT,
            'program' => 'BSIT',
            'year_level' => 2,
        ]);

        ActivityLog::create([
            'user_id' => $student->id,
            'user_email' => $student->email,
            'user_name' => $student->name,
            'user_role' => User::ROLE_STUDENT,
            'event' => ActivityLog::EVENT_LOGIN,
            'occurred_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Taylor Santos')
            ->assertSee('Currently logged-in users')
            ->assertSee('BSIT');
    }

    public function test_student_cannot_use_the_administrator_login(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'secure-password',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'student@example.com',
            'password' => 'secure-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_administrator_can_sign_in_and_open_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Currently logged-in users')
            ->assertSee('Administrator');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'event' => ActivityLog::EVENT_LOGIN,
            'user_role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_archived_student_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'archived@example.com',
            'password' => 'secure-password',
            'role' => User::ROLE_STUDENT,
            'archived_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'archived@example.com',
            'password' => 'secure-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
