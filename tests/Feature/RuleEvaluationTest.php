<?php

use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Enums\UserRole;
use App\Models\ActivitySignal;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\UsageSession;
use App\Models\User;
use App\Rules\Equipment\CapacityPressureRule;
use App\Rules\Equipment\IdleWhilePoweredRule;
use App\Rules\Equipment\NoShowRule;
use App\Rules\Equipment\OverdueRule;
use App\Rules\Equipment\UnderutilizedRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createDemoManagerForRuleEvaluationTests(): User
{
    $manager = User::factory()->create([
        'email' => 'manager.rules@lab.local',
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

test('capacity pressure rule matches utilization over eighty five percent', function () {
    $equipment = Equipment::factory()->create(['utilization_rate' => 90]);

    $result = app(CapacityPressureRule::class)->evaluate($equipment);

    expect($result->matched)->toBeTrue()
        ->and($result->severity)->toBe('warning');
});

test('underutilized rule matches equipment unused for more than sixty days', function () {
    $equipment = Equipment::factory()->create([
        'last_used_at' => now()->subDays(70),
    ]);

    $result = app(UnderutilizedRule::class)->evaluate($equipment);

    expect($result->matched)->toBeTrue()
        ->and($result->evidence['idle_days'])->toBe(70);
});

test('idle while powered rule matches power on without activity for more than thirty minutes', function () {
    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => now()->subMinutes(45),
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => false,
        'recorded_at' => now()->subMinutes(5),
    ]);

    $result = app(IdleWhilePoweredRule::class)->evaluate($equipment);

    expect($result->matched)->toBeTrue()
        ->and($result->evidence['idle_minutes'])->toBe(45);
});

test('no show rule matches expired booking without usage session', function () {
    $equipment = Equipment::factory()->create();
    $user = User::factory()->create();

    Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $user->id,
        'start_time' => now()->subHours(3),
        'end_time' => now()->subHour(),
        'status' => BookingStatus::Booked,
    ]);

    $result = app(NoShowRule::class)->evaluate($equipment);

    expect($result->matched)->toBeTrue()
        ->and($result->severity)->toBe('warning');
});

test('overdue rule matches checked in session beyond booking end time', function () {
    $equipment = Equipment::factory()->create();
    $user = User::factory()->create();

    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $user->id,
        'start_time' => now()->subHours(3),
        'end_time' => now()->subHour(),
        'status' => BookingStatus::CheckedIn,
    ]);

    UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $user->id,
        'started_at' => now()->subHours(3),
        'ended_at' => null,
        'status' => BookingStatus::CheckedIn,
    ]);

    $result = app(OverdueRule::class)->evaluate($equipment);

    expect($result->matched)->toBeTrue()
        ->and($result->severity)->toBe('critical');
});

test('rule evaluation persists abnormal pattern and analysis status', function () {
    createDemoManagerForRuleEvaluationTests();

    $equipment = Equipment::factory()->create([
        'utilization_rate' => 90,
        'current_analysis_status' => AnalysisStatus::Normal,
    ]);

    $this->post(route('equipments.evaluate-rules', $equipment))
        ->assertRedirect(route('equipments.show', $equipment));

    $this->assertDatabaseHas('abnormal_patterns', [
        'equipment_id' => $equipment->id,
        'rule_name' => CapacityPressureRule::class,
        'status' => 'open',
    ]);

    expect($equipment->refresh()->current_analysis_status)->toBe(AnalysisStatus::CapacityPressure);
});

test('rule evaluation updates existing open pattern instead of duplicating it', function () {
    createDemoManagerForRuleEvaluationTests();

    $equipment = Equipment::factory()->create(['utilization_rate' => 90]);

    $this->post(route('equipments.evaluate-rules', $equipment));
    $this->post(route('equipments.evaluate-rules', $equipment));

    expect($equipment->abnormalPatterns()->where('rule_name', CapacityPressureRule::class)->count())->toBe(1);
});
