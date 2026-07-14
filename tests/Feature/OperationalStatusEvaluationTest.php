<?php

use App\Enums\OperationalStatus;
use App\Enums\PowerEventType;
use App\Enums\UserRole;
use App\Models\ActivitySignal;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useLabStaffForOperationalStatusTests(): User
{
    $labStaff = User::factory()->create([
        'email' => 'lab.operational-status@lab.local',
        'role' => UserRole::LabStaff,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $labStaff->email]);

    return $labStaff;
}

test('equipment operational status can be evaluated and persisted from telemetry', function () {
    useLabStaffForOperationalStatusTests();

    $equipment = Equipment::factory()->create([
        'current_operational_status' => OperationalStatus::Off,
    ]);

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => '2026-07-11 09:05:00',
    ]);

    $this->post(route('equipments.evaluate-operational-status', $equipment))
        ->assertRedirect(route('equipments.show', $equipment));

    expect($equipment->refresh()->current_operational_status)->toBe(OperationalStatus::Active);
});

test('equipment show renders operational rule action', function () {
    useLabStaffForOperationalStatusTests();

    $equipment = Equipment::factory()->create();

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tình trạng')
        ->assertSee('Đồng bộ trạng thái');
});

test('researcher sees read only equipment rule context', function () {
    $researcher = User::factory()->create([
        'email' => 'researcher.operational-status@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    $equipment = Equipment::factory()->create();

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tình trạng')
        ->assertDontSee('Đồng bộ trạng thái')
        ->assertDontSee('Đánh giá lại bất thường')
        ->assertDontSee('Tính lại chỉ số 30 ngày');
});
