<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function setDemoNavigationUser(UserRole $role): User
{
    $user = User::factory()->create([
        'name' => match ($role) {
            UserRole::Admin => 'Demo Admin',
            UserRole::Manager => 'Demo Manager',
            UserRole::LabStaff => 'Demo Lab Staff',
            UserRole::Researcher => 'Demo Researcher',
        },
        'email' => $role->value.'.navigation@lab.local',
        'role' => $role,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('manager navigation focuses on utilization analysis workspace', function () {
    setDemoNavigationUser(UserRole::Manager);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('Tổng quan khai thác')
        ->assertSee('Khai thác thiết bị')
        ->assertSee('Tổng quan khai thác')
        ->assertSee('Thiết bị')
        ->assertSee('Bất thường')
        ->assertSee('Lịch sử phân tích')
        ->assertDontSee('Lịch đặt')
        ->assertDontSee('Lab Staff Workspace')
        ->assertDontSee('Researcher Workspace');
});

test('lab staff navigation focuses on daily lab operation', function () {
    setDemoNavigationUser(UserRole::LabStaff);

    $this->get(route('dashboard.lab-staff'))
        ->assertOk()
        ->assertSee('Lab Staff Workspace')
        ->assertSee('Thiết bị')
        ->assertSee('Lịch đặt')
        ->assertSee('Phiên sử dụng')
        ->assertDontSee('Tổng quan khai thác')
        ->assertDontSee('Lịch sử phân tích');
});

test('researcher navigation focuses on equipment booking', function () {
    setDemoNavigationUser(UserRole::Researcher);

    $this->get(route('dashboard.researcher'))
        ->assertOk()
        ->assertSee('Researcher Workspace')
        ->assertSee('Thiết bị')
        ->assertSee('Lịch đặt của tôi')
        ->assertDontSee('Tổng quan khai thác')
        ->assertDontSee('Lịch sử phân tích');
});

test('admin navigation focuses on system workspace', function () {
    setDemoNavigationUser(UserRole::Admin);

    $this->get(route('dashboard.admin'))
        ->assertOk()
        ->assertSee('Tổng quan quản trị')
        ->assertSee('Quản trị')
        ->assertSee('Tổng quan')
        ->assertSee('Thiết bị')
        ->assertSee('Lịch sử phân tích')
        ->assertDontSee('Lịch đặt')
        ->assertDontSee('Phiên sử dụng')
        ->assertDontSee('Manager Workspace')
        ->assertDontSee('Lab Staff Workspace')
        ->assertDontSee('Researcher Workspace');
});
