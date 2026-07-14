<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('app shell renders responsive sidebar controls and user menu', function () {
    $manager = User::factory()->create([
        'name' => 'Responsive Manager',
        'email' => 'manager.shell@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('data-app-shell', false)
        ->assertSee('data-sidebar-toggle', false)
        ->assertSee('data-sidebar-open', false)
        ->assertSee('Đổi tài khoản demo')
        ->assertSee('Responsive Manager')
        ->assertSee('Tổng quan khai thác')
        ->assertDontSee('Lab Staff Workspace');
});
