<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useModalActionUser(UserRole $role): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.modal-action@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('researcher dashboard exposes create booking modal action', function () {
    useModalActionUser(UserRole::Researcher);
    Equipment::factory()->create(['equipment_code' => 'MODAL-BOOK-001']);

    $this->get(route('dashboard.researcher'))
        ->assertOk()
        ->assertSee('data-modal-open="create-booking-modal"', false)
        ->assertSee('data-ajax-form', false)
        ->assertSee('MODAL-BOOK-001');
});

test('ajax booking validation uses standard error payload', function () {
    useModalActionUser(UserRole::Researcher);

    $this->postJson(route('bookings.store'), [])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Dữ liệu không hợp lệ.')
        ->assertJsonStructure(['errors' => ['equipment_id', 'user_id', 'start_time', 'end_time']]);
});

test('lab staff can check in booking through ajax action', function () {
    $labStaff = useModalActionUser(UserRole::LabStaff);
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create();
    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::Booked,
    ]);

    $this->postJson(route('bookings.check-in', $booking))
        ->assertOk()
        ->assertJsonPath('message', 'Check-in thành công.')
        ->assertJsonStructure(['data' => ['usage_session_id'], 'refresh' => ['target']]);
});
