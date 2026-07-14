<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\LabStaffDashboardService;
use App\Services\Dashboard\ManagerDashboardService;
use App\Services\Dashboard\ResearcherDashboardService;

test('user role helpers identify each role', function () {
    $admin = new User(['role' => UserRole::Admin]);
    $manager = new User(['role' => UserRole::Manager]);
    $labStaff = new User(['role' => UserRole::LabStaff]);
    $researcher = new User(['role' => UserRole::Researcher]);

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isManager())->toBeFalse()
        ->and($admin->isLabStaff())->toBeFalse()
        ->and($admin->isResearcher())->toBeFalse()
        ->and($manager->isManager())->toBeTrue()
        ->and($labStaff->isLabStaff())->toBeTrue()
        ->and($researcher->isResearcher())->toBeTrue();
});

test('user capability helpers match agreed role responsibilities', function () {
    $admin = new User(['role' => UserRole::Admin]);
    $manager = new User(['role' => UserRole::Manager]);
    $labStaff = new User(['role' => UserRole::LabStaff]);
    $researcher = new User(['role' => UserRole::Researcher]);

    expect($admin->canManageSystem())->toBeTrue()
        ->and($admin->canOperateLab())->toBeTrue()
        ->and($admin->canViewManagementDashboard())->toBeTrue()
        ->and($manager->canManageSystem())->toBeFalse()
        ->and($manager->canOperateLab())->toBeFalse()
        ->and($manager->canViewManagementDashboard())->toBeTrue()
        ->and($labStaff->canOperateLab())->toBeTrue()
        ->and($labStaff->canViewManagementDashboard())->toBeFalse()
        ->and($researcher->canOperateLab())->toBeFalse()
        ->and($researcher->canViewManagementDashboard())->toBeFalse()
        ->and($manager->canUseBookingWorkspace())->toBeFalse()
        ->and($labStaff->canUseBookingWorkspace())->toBeTrue()
        ->and($researcher->canUseBookingWorkspace())->toBeTrue();
});

test('user role label is readable for role aware navigation', function () {
    expect((new User(['role' => UserRole::Admin]))->roleLabel())->toBe('Quản trị viên')
        ->and((new User(['role' => UserRole::Manager]))->roleLabel())->toBe('Quản lý phòng thí nghiệm')
        ->and((new User(['role' => UserRole::LabStaff]))->roleLabel())->toBe('Kỹ thuật viên')
        ->and((new User(['role' => UserRole::Researcher]))->roleLabel())->toBe('Người nghiên cứu / Sinh viên');
});

test('dashboard services expose role specific summary contracts', function () {
    $user = new User(['name' => 'Demo User', 'role' => UserRole::Manager]);

    expect((new AdminDashboardService)->summaryFor($user)->cards['scope'])->toBe('system_configuration')
        ->and((new ManagerDashboardService)->summaryFor($user)->cards['scope'])->toBe('utilization_analysis')
        ->and((new LabStaffDashboardService)->summaryFor($user)->cards['scope'])->toBe('daily_operations')
        ->and((new ResearcherDashboardService)->summaryFor($user)->cards['scope'])->toBe('personal_equipment_usage');
});
