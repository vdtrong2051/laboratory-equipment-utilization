<?php

use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoLabStaffForTelemetryTests(): User
{
    $labStaff = User::factory()->create([
        'name' => 'Telemetry Lab Staff',
        'email' => 'telemetry.staff@lab.local',
        'role' => UserRole::LabStaff,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $labStaff->email]);

    return $labStaff;
}

test('telemetry simulator creates power on and activity signals for checked in session', function () {
    $labStaff = createDemoLabStaffForTelemetryTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'TEL-001',
        'laboratory' => 'Lab A',
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
        'started_at' => '2026-07-11 09:00:00',
        'ended_at' => null,
        'status' => BookingStatus::CheckedIn,
    ]);

    $this->postJson(route('usage-sessions.simulate-telemetry', $usageSession), [
        'samples' => 8,
    ])
        ->assertOk()
        ->assertJsonPath('summary.power_events_created', 1)
        ->assertJsonPath('summary.activity_signals_created', 8);

    $this->assertDatabaseHas('power_events', [
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn->value,
        'source' => 'simulated',
        'gateway_id' => 'SIM-GW-Lab A',
    ]);

    $this->assertDatabaseCount('activity_signals', 8);
    $this->assertDatabaseHas('activity_signals', [
        'equipment_id' => $equipment->id,
        'usage_session_id' => $usageSession->id,
        'signal_type' => 'current_sensor',
        'source' => 'simulated',
    ]);
});

test('telemetry simulator creates power off for completed session', function () {
    $labStaff = createDemoLabStaffForTelemetryTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'TEL-002']);

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::Completed,
    ]);

    $usageSession = UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'checked_in_by' => $labStaff->id,
        'completed_by' => $labStaff->id,
        'started_at' => '2026-07-11 09:00:00',
        'ended_at' => '2026-07-11 10:30:00',
        'status' => BookingStatus::Completed,
    ]);

    $this->postJson(route('usage-sessions.simulate-telemetry', $usageSession), [
        'samples' => 5,
    ])
        ->assertOk()
        ->assertJsonPath('summary.power_events_created', 2)
        ->assertJsonPath('summary.activity_signals_created', 5);

    $this->assertDatabaseHas('power_events', [
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOff->value,
        'source' => 'simulated',
    ]);
});

test('usage session detail shows telemetry simulator controls', function () {
    $labStaff = createDemoLabStaffForTelemetryTests();
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'TEL-SCREEN-001']);

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

    $this->get(route('usage-sessions.show', $usageSession))
        ->assertOk()
        ->assertSee('Telemetry mô phỏng')
        ->assertSee('Sinh telemetry mô phỏng')
        ->assertSee('Activity signals gần nhất');
});
