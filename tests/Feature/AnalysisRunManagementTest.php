<?php

use App\Enums\UserRole;
use App\Models\AnalysisRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoManagerForAnalysisRunTests(): User
{
    $manager = User::factory()->create([
        'name' => 'Analysis Run Manager',
        'email' => 'analysis.run.manager@lab.local',
        'role' => UserRole::Manager,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    return $manager;
}

function createDemoAdminForAnalysisRunTests(): User
{
    $admin = User::factory()->create([
        'name' => 'Analysis Run Admin',
        'email' => 'analysis.run.admin@lab.local',
        'role' => UserRole::Admin,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $admin->email]);

    return $admin;
}

test('analysis run index renders runs and filters', function () {
    $manager = createDemoManagerForAnalysisRunTests();

    AnalysisRun::factory()->create([
        'status' => 'completed',
        'trigger_source' => 'web',
        'triggered_by' => $manager->id,
        'processed_equipment' => 7,
        'matched_rules' => 3,
    ]);

    $this->get(route('analysis-runs.index', ['status' => 'completed', 'trigger_source' => 'web']))
        ->assertOk()
        ->assertSee('Lịch sử phân tích')
        ->assertSee('Theo dõi các lần phân tích dữ liệu sử dụng thiết bị')
        ->assertSee('completed')
        ->assertSee('web')
        ->assertSee('7')
        ->assertSee('Chi tiết');
});

test('analysis run show renders summary and error context', function () {
    createDemoManagerForAnalysisRunTests();

    $analysisRun = AnalysisRun::factory()->create([
        'status' => 'failed',
        'trigger_source' => 'artisan',
        'processed_equipment' => 2,
        'matched_rules' => 1,
        'summary' => ['processed_equipment' => 2, 'matched_rules' => 1],
        'error_message' => 'Demo failure',
    ]);

    $this->get(route('analysis-runs.show', $analysisRun))
        ->assertOk()
        ->assertSee('Chi tiết Analysis Run')
        ->assertSee('failed')
        ->assertSee('Demo failure')
        ->assertSee('Summary JSON')
        ->assertSee('processed_equipment');
});

test('analysis run empty state returns to current role dashboard', function () {
    createDemoAdminForAnalysisRunTests();

    $this->get(route('analysis-runs.index'))
        ->assertOk()
        ->assertSee('Mở dashboard vai trò')
        ->assertSee(route('dashboard.admin'), false)
        ->assertDontSee(route('dashboard.manager'), false);
});

test('manager dashboard links to analysis run detail and full history', function () {
    createDemoManagerForAnalysisRunTests();
    $analysisRun = AnalysisRun::factory()->create([
        'status' => 'completed',
        'trigger_source' => 'web',
    ]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('Xem tất cả')
        ->assertSee(route('analysis-runs.index'), false)
        ->assertSee(route('analysis-runs.show', $analysisRun), false);
});
