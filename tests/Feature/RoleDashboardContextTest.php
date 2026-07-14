<?php

use App\Enums\BookingStatus;
use App\Enums\OperationalStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useDashboardRole(UserRole $role): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.dashboard-context@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('admin dashboard renders system context metrics', function () {
    useDashboardRole(UserRole::Admin);
    Equipment::factory()->count(2)->create();

    $this->get(route('dashboard.admin'))
        ->assertOk()
        ->assertSee('Người dùng hoạt động')
        ->assertSee('Thiết bị')
        ->assertSee('Tình trạng hệ thống')
        ->assertSee('Người dùng theo vai trò')
        ->assertSee('Người dùng hệ thống');
});

test('lab staff dashboard renders operational context', function () {
    $labStaff = useDashboardRole(UserRole::LabStaff);
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'LAB-DASH-001',
        'current_operational_status' => OperationalStatus::PoweredIdle,
    ]);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHour(),
        'status' => BookingStatus::CheckedIn,
    ]);

    UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'checked_in_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);

    $this->get(route('dashboard.lab-staff'))
        ->assertOk()
        ->assertSee('Booking cần theo dõi')
        ->assertSee('Phiên đang chạy')
        ->assertSee('LAB-DASH-001')
        ->assertSee('Thiết bị bật nhưng idle');
});

test('researcher dashboard renders personal booking context', function () {
    $researcher = useDashboardRole(UserRole::Researcher);
    $equipment = Equipment::factory()->create(['equipment_code' => 'RES-DASH-001']);

    Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => BookingStatus::Booked,
        'purpose' => 'Kiểm tra dashboard cá nhân',
    ]);

    $this->get(route('dashboard.researcher'))
        ->assertOk()
        ->assertSee('Booking của tôi')
        ->assertSee('Lịch sử dụng của tôi')
        ->assertSee('RES-DASH-001')
        ->assertSee('Kiểm tra dashboard cá nhân');
});
