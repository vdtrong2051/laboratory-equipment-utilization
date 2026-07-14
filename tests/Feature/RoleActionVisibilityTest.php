<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('researcher booking detail hides lab operation actions', function () {
    $researcher = User::factory()->create([
        'email' => 'researcher.action-visibility@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    $equipment = Equipment::factory()->create();
    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked,
    ]);

    UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'status' => BookingStatus::CheckedIn,
    ]);

    $this->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertDontSee('Check-in')
        ->assertDontSee('Mở phiên sử dụng')
        ->assertDontSee('Xem phiên sử dụng');
});

test('manager dashboard hides lab operation shortcuts', function () {
    $manager = User::factory()->create([
        'email' => 'manager.action-visibility@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertDontSee('Phiên sử dụng');
});

test('researcher equipment index hides system management actions', function () {
    $researcher = User::factory()->create([
        'email' => 'researcher.equipment-actions@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    Equipment::factory()->create(['equipment_code' => 'READ-ONLY-001']);

    $this->get(route('equipments.index'))
        ->assertOk()
        ->assertSee('READ-ONLY-001')
        ->assertDontSee('Thêm thiết bị')
        ->assertDontSee('Sửa');
});

test('researcher booking create form is personalized to current user', function () {
    $researcher = User::factory()->create([
        'name' => 'Researcher Self Booking',
        'email' => 'researcher.booking-form@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);
    $otherUser = User::factory()->create([
        'name' => 'Another Researcher',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    Equipment::factory()->create(['equipment_code' => 'FORM-BOOK-001']);

    $this->get(route('bookings.create'))
        ->assertOk()
        ->assertSee('Researcher Self Booking')
        ->assertSee('Lịch đặt này sẽ được tạo cho chính bạn')
        ->assertDontSee('Another Researcher');
});
