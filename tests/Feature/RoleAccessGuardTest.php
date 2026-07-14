<?php

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useDemoRoleForAccessGuard(UserRole $role): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.access@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('manager cannot access booking workspace directly', function () {
    useDemoRoleForAccessGuard(UserRole::Manager);

    $this->get(route('bookings.index'))
        ->assertForbidden()
        ->assertSee('Không có quyền truy cập')
        ->assertSee('Vai trò demo hiện tại không thể truy cập workspace này.')
        ->assertSee('Về dashboard vai trò');
});

test('researcher cannot access lab operation workspace directly', function () {
    useDemoRoleForAccessGuard(UserRole::Researcher);

    $this->get(route('usage-sessions.index'))
        ->assertForbidden()
        ->assertSee('Không có quyền truy cập')
        ->assertSee('Đổi tài khoản demo');
});

test('lab staff cannot access management analysis directly', function () {
    useDemoRoleForAccessGuard(UserRole::LabStaff);

    $this->get(route('analysis-runs.index'))->assertForbidden();
});

test('researcher cannot mutate equipment directly', function () {
    useDemoRoleForAccessGuard(UserRole::Researcher);

    $equipment = Equipment::factory()->create();

    $this->get(route('equipments.create'))->assertForbidden();
    $this->get(route('equipments.edit', $equipment))->assertForbidden();
    $this->post(route('equipments.evaluate-operational-status', $equipment))->assertForbidden();
});

test('manager cannot mutate operational equipment state directly', function () {
    useDemoRoleForAccessGuard(UserRole::Manager);

    $equipment = Equipment::factory()->create();

    $this->post(route('equipments.evaluate-operational-status', $equipment))->assertForbidden();
});

test('manager can access management workspace', function () {
    useDemoRoleForAccessGuard(UserRole::Manager);

    $this->get(route('dashboard.manager'))->assertOk();
});

test('admin can access every guarded workspace', function () {
    useDemoRoleForAccessGuard(UserRole::Admin);

    $this->get(route('bookings.index'))->assertOk();
    $this->get(route('usage-sessions.index'))->assertOk();
    $this->get(route('analysis-runs.index'))->assertOk();
    $this->get(route('dashboard.admin'))->assertOk();
});
