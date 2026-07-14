<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoLabStaffForUsageSessionTests(): User
{
    $labStaff = User::factory()->create([
        'name' => 'Demo Lab Staff',
        'email' => 'lab.staff@lab.local',
        'role' => UserRole::LabStaff,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $labStaff->email]);

    return $labStaff;
}

test('booked booking can be checked in and creates usage session', function () {
    $labStaff = createDemoLabStaffForUsageSessionTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'CHECKIN-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::Booked,
    ]);

    $this->post(route('bookings.check-in', $booking))
        ->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::CheckedIn);

    $usageSession = UsageSession::query()->firstOrFail();

    expect($usageSession->booking_id)->toBe($booking->id)
        ->and($usageSession->equipment_id)->toBe($equipment->id)
        ->and($usageSession->user_id)->toBe($researcher->id)
        ->and($usageSession->checked_in_by)->toBe($labStaff->id)
        ->and($usageSession->status)->toBe(BookingStatus::CheckedIn)
        ->and($usageSession->started_at)->not->toBeNull();
});

test('non booked booking cannot be checked in again', function () {
    $labStaff = createDemoLabStaffForUsageSessionTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'CHECKIN-REJECT-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::Completed,
    ]);

    $this->from(route('bookings.show', $booking))
        ->post(route('bookings.check-in', $booking))
        ->assertRedirect(route('bookings.show', $booking))
        ->assertSessionHasErrors('booking');

    $this->assertDatabaseCount('usage_sessions', 0);
});

test('checked in usage session can be completed', function () {
    $labStaff = createDemoLabStaffForUsageSessionTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'COMPLETE-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);

    $usageSession = UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'checked_in_by' => $labStaff->id,
        'completed_by' => null,
        'ended_at' => null,
        'status' => BookingStatus::CheckedIn,
    ]);

    $this->post(route('usage-sessions.complete', $usageSession))
        ->assertRedirect(route('usage-sessions.show', $usageSession));

    expect($usageSession->refresh()->status)->toBe(BookingStatus::Completed)
        ->and($usageSession->completed_by)->toBe($labStaff->id)
        ->and($usageSession->ended_at)->not->toBeNull()
        ->and($booking->refresh()->status)->toBe(BookingStatus::Completed);
});

test('usage session screens render records', function () {
    $labStaff = createDemoLabStaffForUsageSessionTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'SESSION-INDEX-001',
        'name' => 'Thiết bị kiểm thử phiên',
    ]);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);

    $usageSession = UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'checked_in_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);

    $this->get(route('usage-sessions.index'))
        ->assertOk()
        ->assertSee($equipment->equipment_code)
        ->assertSee('CHECKED_IN');

    $this->get(route('usage-sessions.show', $usageSession))
        ->assertOk()
        ->assertSee($equipment->name)
        ->assertSee($researcher->name);
});
