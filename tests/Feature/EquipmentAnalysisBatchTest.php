<?php

use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Enums\UserRole;
use App\Models\ActivitySignal;
use App\Models\AnalysisRun;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\User;
use App\Services\EquipmentAnalysisBatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-07-11 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function createDemoManagerForBatchTests(): User
{
    $manager = User::factory()->create([
        'name' => 'Batch Manager',
        'email' => 'batch.manager@lab.local',
        'role' => UserRole::Manager,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    return $manager;
}

test('equipment analysis batch processes every equipment and persists metrics patterns and run log', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'BATCH-001',
        'utilization_rate' => 0,
    ]);
    Equipment::factory()->create(['equipment_code' => 'BATCH-002']);

    Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $user->id,
        'start_time' => now()->subHours(3),
        'end_time' => now()->subHour(),
        'status' => BookingStatus::Completed,
    ]);

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => now()->subHours(2),
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => false,
        'recorded_at' => now()->subMinutes(10),
    ]);

    $summary = app(EquipmentAnalysisBatchService::class)->run(
        now()->subDays(30)->startOfDay(),
        now()->endOfDay(),
        triggerSource: 'test',
    );

    expect($summary['processed_equipment'])->toBe(2)
        ->and($summary['operational_status_evaluated'])->toBe(2)
        ->and($summary['usage_metrics_calculated'])->toBe(2)
        ->and($summary['rule_sets_evaluated'])->toBe(2)
        ->and($summary['matched_rules'])->toBeGreaterThan(0)
        ->and($summary['analysis_run_id'])->toBeInt();

    $this->assertDatabaseHas('usage_metrics', ['equipment_id' => $equipment->id]);
    $this->assertDatabaseHas('abnormal_patterns', [
        'equipment_id' => $equipment->id,
        'status' => 'open',
    ]);
    $this->assertDatabaseHas('analysis_runs', [
        'id' => $summary['analysis_run_id'],
        'status' => 'completed',
        'trigger_source' => 'test',
        'processed_equipment' => 2,
    ]);

    expect($equipment->refresh()->current_analysis_status)->toBe(AnalysisStatus::IdleWhilePowered);
});

test('manager dashboard can run analysis batch from web', function () {
    $manager = createDemoManagerForBatchTests();
    Equipment::factory()->create(['equipment_code' => 'BATCH-WEB-001']);

    $this->post(route('dashboard.manager.run-analysis'))
        ->assertRedirect(route('dashboard.manager'))
        ->assertSessionHas('status');

    $this->assertDatabaseCount('usage_metrics', 1);
    $this->assertDatabaseHas('analysis_runs', [
        'status' => 'completed',
        'trigger_source' => 'web',
        'triggered_by' => $manager->id,
        'processed_equipment' => 1,
    ]);
});

test('equipment analyze artisan command runs batch analysis', function () {
    Equipment::factory()->count(2)->create();

    $this->artisan('equipment:analyze --days=15')
        ->expectsOutput('Equipment analysis completed.')
        ->assertExitCode(0);

    $this->assertDatabaseCount('usage_metrics', 2);
    $this->assertDatabaseHas('analysis_runs', [
        'status' => 'completed',
        'trigger_source' => 'artisan',
        'processed_equipment' => 2,
    ]);
});

test('manager dashboard renders batch analysis action and run history', function () {
    createDemoManagerForBatchTests();
    AnalysisRun::factory()->create([
        'trigger_source' => 'web',
        'processed_equipment' => 3,
        'matched_rules' => 2,
    ]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('Chạy phân tích')
        ->assertSee('Phân tích gần nhất')
        ->assertSee(route('dashboard.manager.run-analysis'), false);
});
