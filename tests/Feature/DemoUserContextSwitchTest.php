<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo login page renders active users by role', function () {
    User::factory()->create([
        'name' => 'Login Manager',
        'email' => 'manager.login@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    $this->get(route('demo-login.index'))
        ->assertOk()
        ->assertSee('Chọn tài khoản trải nghiệm')
        ->assertSee('Chế độ trình diễn')
        ->assertSee('không yêu cầu mật khẩu')
        ->assertSee('Quản lý phòng thí nghiệm')
        ->assertSee('MANAGER')
        ->assertSee('Quay lại đăng nhập')
        ->assertSee('Login Manager')
        ->assertSee('Đăng nhập');
});

test('demo login stores selected user in session', function () {
    $manager = User::factory()->create([
        'name' => 'Demo Manager Login',
        'email' => 'manager.login-session@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    $this->post(route('demo-login.login'), [
        'user_id' => $manager->id,
    ])
        ->assertRedirect(route('dashboard.manager'))
        ->assertSessionHas('demo_user_id', $manager->id);
});

test('demo logout clears selected user session', function () {
    $manager = User::factory()->create([
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    $this->withSession(['demo_user_id' => $manager->id])
        ->post(route('demo-login.logout'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('demo_user_id');
});

test('demo role switch stores selected user in session and redirects to role dashboard', function () {
    $manager = User::factory()->create([
        'name' => 'Demo Manager',
        'email' => 'manager.switch@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    $researcher = User::factory()->create([
        'name' => 'Demo Researcher',
        'email' => 'researcher.switch@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    $this->post(route('demo-context.switch-role'), [
        'role' => UserRole::Researcher->value,
    ])
        ->assertRedirect(route('dashboard.researcher'))
        ->assertSessionHas('demo_user_id', $researcher->id);

    $this->withSession(['demo_user_id' => $researcher->id])
        ->get(route('dashboard.researcher'))
        ->assertOk()
        ->assertSee('Demo Researcher')
        ->assertSee('Người nghiên cứu / Sinh viên')
        ->assertSee('Lịch đặt')
        ->assertDontSee('Lịch sử phân tích');
});

test('demo role switch rejects unknown role', function () {
    $this->post(route('demo-context.switch-role'), [
        'role' => 'unknown',
    ])->assertSessionHasErrors('role');
});
