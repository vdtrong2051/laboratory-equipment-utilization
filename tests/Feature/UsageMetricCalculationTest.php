<?php

use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Enums\UserRole;
use App\Models\ActivitySignal;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\User;
use App\Services\UsageMetricService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createDemoManagerForUsageMetricCalculationTests(): User
{
    $manager = User::factory()->create([
        'email' => 'manager.metrics@lab.local',
        'role' => UserRole::Manager,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    return $manager;
}

beforeEach(function () {
    Carbon::setTestNow('2026-07-11 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('usage metric service calculates booked powered active and idle minutes', function () {
    $equipment = Equipment::factory()->create();
    $user = User::factory()->create();
    $periodStart = now()->startOfDay();
    $periodEnd = now()->endOfDay();

    Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $user->id,
        'start_time' => '2026-07-11 09:00:00',
        'end_time' => '2026-07-11 11:00:00',
        'status' => BookingStatus::Completed,
    ]);

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOff,
        'recorded_at' => '2026-07-11 11:00:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => '2026-07-11 09:15:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => '2026-07-11 09:45:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => false,
        'recorded_at' => '2026-07-11 10:15:00',
    ]);

    $metric = app(UsageMetricService::class)->calculateForEquipment($equipment, $periodStart, $periodEnd);

    expect($metric->total_booked_minutes)->toBe(120)
        ->and($metric->total_powered_minutes)->toBe(120)
        ->and($metric->total_active_minutes)->toBe(30)
        ->and($metric->total_idle_minutes)->toBe(90)
        ->and((float) $metric->powered_idle_rate)->toBe(75.0);
});

test('equipment route calculates usage metrics and updates utilization rate', function () {
    createDemoManagerForUsageMetricCalculationTests();

    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => now()->subHours(2),
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => now()->subHour(),
    ]);

    $this->post(route('equipments.calculate-usage-metrics', $equipment))
        ->assertRedirect(route('equipments.show', $equipment));

    $this->assertDatabaseHas('usage_metrics', [
        'equipment_id' => $equipment->id,
    ]);

    expect((float) $equipment->refresh()->utilization_rate)->toBeGreaterThan(0);
});

test('equipment show renders usage metric controls and table', function () {
    createDemoManagerForUsageMetricCalculationTests();

    $equipment = Equipment::factory()->create();

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tính lại chỉ số 30 ngày')
        ->assertSee('Chỉ số khai thác');
});
