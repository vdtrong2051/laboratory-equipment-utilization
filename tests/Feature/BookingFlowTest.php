<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoResearcherForBookingTests(): User
{
    $researcher = User::factory()->create([
        'name' => 'Demo Researcher',
        'email' => 'researcher.booking@lab.local',
        'role' => UserRole::Researcher,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    return $researcher;
}

test('booking index renders existing bookings', function () {
    $researcher = createDemoResearcherForBookingTests();
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-INDEX-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked,
        'purpose' => 'Kiểm tra danh sách booking',
    ]);

    $this->get(route('bookings.index'))
        ->assertOk()
        ->assertSee($equipment->equipment_code)
        ->assertSee($booking->purpose);
});

test('researcher booking index is scoped to own bookings', function () {
    $researcher = createDemoResearcherForBookingTests();
    $otherUser = User::factory()->create(['role' => UserRole::Researcher]);
    $ownEquipment = Equipment::factory()->create(['equipment_code' => 'BOOK-OWN-001']);
    $otherEquipment = Equipment::factory()->create(['equipment_code' => 'BOOK-OTHER-001']);

    Booking::factory()->create([
        'equipment_id' => $ownEquipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked,
        'purpose' => 'Booking của tôi',
    ]);

    Booking::factory()->create([
        'equipment_id' => $otherEquipment->id,
        'user_id' => $otherUser->id,
        'created_by' => $otherUser->id,
        'status' => BookingStatus::Booked,
        'purpose' => 'Booking của người khác',
    ]);

    $this->get(route('bookings.index'))
        ->assertOk()
        ->assertSee('BOOK-OWN-001')
        ->assertSee('Booking của tôi')
        ->assertDontSee('Booking của người khác');
});

test('researcher cannot view another user booking directly', function () {
    createDemoResearcherForBookingTests();
    $otherUser = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-FORBIDDEN-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $otherUser->id,
        'created_by' => $otherUser->id,
        'status' => BookingStatus::Booked,
    ]);

    $this->get(route('bookings.show', $booking))
        ->assertForbidden()
        ->assertSee('Không có quyền truy cập');
});

test('booking can be created when schedule does not conflict', function () {
    $researcher = createDemoResearcherForBookingTests();
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-CREATE-001']);

    $this->post(route('bookings.store'), [
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'start_time' => '2026-08-01 09:00:00',
        'end_time' => '2026-08-01 11:00:00',
        'purpose' => 'Tạo lịch đặt từ test',
    ])->assertRedirect();

    $this->assertDatabaseHas('bookings', [
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked->value,
        'purpose' => 'Tạo lịch đặt từ test',
    ]);
});

test('researcher booking creation is forced to current user', function () {
    $researcher = createDemoResearcherForBookingTests();
    $otherUser = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-FORCE-USER-001']);

    $this->post(route('bookings.store'), [
        'equipment_id' => $equipment->id,
        'user_id' => $otherUser->id,
        'start_time' => '2026-08-02 09:00:00',
        'end_time' => '2026-08-02 11:00:00',
        'purpose' => 'Không được tạo hộ người khác',
    ])->assertRedirect();

    $this->assertDatabaseHas('bookings', [
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'purpose' => 'Không được tạo hộ người khác',
    ]);

    $this->assertDatabaseMissing('bookings', [
        'equipment_id' => $equipment->id,
        'user_id' => $otherUser->id,
        'purpose' => 'Không được tạo hộ người khác',
    ]);
});

test('booking creation rejects overlapping schedule for the same equipment', function () {
    $researcher = createDemoResearcherForBookingTests();
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-CONFLICT-001']);

    Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'start_time' => '2026-08-01 09:00:00',
        'end_time' => '2026-08-01 11:00:00',
        'status' => BookingStatus::Booked,
    ]);

    $this->post(route('bookings.store'), [
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'start_time' => '2026-08-01 10:00:00',
        'end_time' => '2026-08-01 12:00:00',
        'purpose' => 'Lịch bị trùng',
    ])->assertSessionHasErrors('start_time');
});

test('booked booking can be cancelled', function () {
    $researcher = createDemoResearcherForBookingTests();
    $equipment = Equipment::factory()->create(['equipment_code' => 'BOOK-CANCEL-001']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked,
        'cancelled_at' => null,
    ]);

    $this->delete(route('bookings.destroy', $booking))
        ->assertRedirect(route('bookings.index'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->cancelled_at)->not->toBeNull();
});
