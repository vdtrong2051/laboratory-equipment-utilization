<?php

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UserRole;
use App\Models\AbnormalPattern;
use App\Models\Equipment;
use App\Models\UsageMetric;
use App\Models\User;
use App\Services\Dashboard\ManagerDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoManagerForDashboardTests(): User
{
    $manager = User::factory()->create([
        'name' => 'Manager Dashboard User',
        'email' => 'manager.dashboard@lab.local',
        'role' => UserRole::Manager,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    return $manager;
}

test('manager dashboard service summarizes equipment metrics and abnormalities', function () {
    $manager = createDemoManagerForDashboardTests();
    $capacityEquipment = Equipment::factory()->create([
        'equipment_code' => 'DASH-CAP-001',
        'current_operational_status' => OperationalStatus::Active,
        'current_analysis_status' => AnalysisStatus::CapacityPressure,
        'utilization_rate' => 91,
    ]);
    Equipment::factory()->create([
        'equipment_code' => 'DASH-UNDER-001',
        'current_analysis_status' => AnalysisStatus::Underutilized,
    ]);

    UsageMetric::factory()->create([
        'equipment_id' => $capacityEquipment->id,
        'actual_utilization_rate' => 20,
        'powered_idle_rate' => 40,
    ]);

    AbnormalPattern::factory()->create([
        'equipment_id' => $capacityEquipment->id,
        'usage_session_id' => null,
        'rule_name' => 'DemoCapacityRule',
        'status' => 'open',
        'severity' => 'warning',
    ]);

    $summary = app(ManagerDashboardService::class)->summaryFor($manager);

    expect($summary->cards['equipment_count'])->toBe(2)
        ->and($summary->cards['open_abnormal_count'])->toBe(1)
        ->and($summary->cards['active_equipment_count'])->toBe(1)
        ->and($summary->cards['capacity_pressure_count'])->toBe(1)
        ->and($summary->cards['underutilized_count'])->toBe(1)
        ->and($summary->cards['average_actual_utilization_rate'])->toBe(20.0)
        ->and($summary->tables['attention_equipments'])->toHaveCount(2)
        ->and($summary->tables['open_abnormal_patterns'])->toHaveCount(1)
        ->and($summary->tables['latest_usage_metrics'])->toHaveCount(1);
});

test('manager dashboard page renders insight, next actions and drill down tables', function () {
    $manager = createDemoManagerForDashboardTests();
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'DASH-VIEW-001',
        'name' => 'Thiết bị dashboard',
        'current_analysis_status' => AnalysisStatus::CapacityPressure,
        'utilization_rate' => 92,
    ]);

    AbnormalPattern::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'rule_name' => 'DemoDashboardRule',
        'status' => 'open',
        'message' => 'Bất thường dashboard',
    ]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('Tổng quan khai thác')
        ->assertSee('Nhận định')
        ->assertSee('Việc cần xử lý')
        ->assertSee('Thiết bị ưu tiên')
        ->assertSee('Phân tích gần nhất')
        ->assertSee('DASH-VIEW-001')
        ->assertSee('Bất thường dashboard');

    expect($manager->exists)->toBeTrue();
});
