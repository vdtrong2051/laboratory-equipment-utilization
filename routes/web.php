<?php

use App\Http\Controllers\AbnormalPatternController;
use App\Http\Controllers\AnalysisRunController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoUserContextController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsageSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/demo-login', [DemoUserContextController::class, 'index'])->name('demo-login.index');
Route::post('/demo-login', [DemoUserContextController::class, 'login'])->name('demo-login.login');
Route::post('/demo-logout', [DemoUserContextController::class, 'logout'])->name('demo-login.logout');

Route::post('/demo-context/role', [DemoUserContextController::class, 'switchRole'])->name('demo-context.switch-role');

Route::middleware('demo.auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'home'])->name('home');

    Route::post('equipments/{equipment}/evaluate-operational-status', [EquipmentController::class, 'evaluateOperationalStatus'])
        ->middleware('demo.capability:lab')
        ->name('equipments.evaluate-operational-status');
    Route::post('equipments/{equipment}/evaluate-rules', [EquipmentController::class, 'evaluateRules'])
        ->middleware('demo.capability:management')
        ->name('equipments.evaluate-rules');
    Route::post('equipments/{equipment}/calculate-usage-metrics', [EquipmentController::class, 'calculateUsageMetrics'])
        ->middleware('demo.capability:management')
        ->name('equipments.calculate-usage-metrics');
    Route::resource('equipments', EquipmentController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('demo.capability:system');
    Route::resource('equipments', EquipmentController::class)->only(['index', 'show']);

    Route::middleware('demo.capability:booking')->group(function (): void {
        Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::get('/dashboard/researcher', [DashboardController::class, 'researcher'])->name('dashboard.researcher');
    });

    Route::middleware('demo.capability:lab')->group(function (): void {
        Route::post('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('usage-sessions/{usageSession}/complete', [UsageSessionController::class, 'complete'])->name('usage-sessions.complete');
        Route::post('usage-sessions/{usageSession}/simulate-telemetry', [UsageSessionController::class, 'simulateTelemetry'])->name('usage-sessions.simulate-telemetry');
        Route::resource('usage-sessions', UsageSessionController::class)
            ->only(['index', 'show'])
            ->parameters(['usage-sessions' => 'usageSession']);
        Route::get('/dashboard/lab-staff', [DashboardController::class, 'labStaff'])->name('dashboard.lab-staff');
    });

    Route::middleware('demo.capability:management')->group(function (): void {
        Route::post('abnormal-patterns/{abnormalPattern}/review', [AbnormalPatternController::class, 'review'])->name('abnormal-patterns.review');
        Route::post('abnormal-patterns/{abnormalPattern}/resolve', [AbnormalPatternController::class, 'resolve'])->name('abnormal-patterns.resolve');
        Route::resource('abnormal-patterns', AbnormalPatternController::class)
            ->only(['index', 'show'])
            ->parameters(['abnormal-patterns' => 'abnormalPattern']);
        Route::resource('analysis-runs', AnalysisRunController::class)
            ->only(['index', 'show'])
            ->parameters(['analysis-runs' => 'analysisRun']);
        Route::get('/dashboard/manager', [DashboardController::class, 'manager'])->name('dashboard.manager');
        Route::post('/dashboard/manager/run-analysis', [DashboardController::class, 'runManagerAnalysis'])->name('dashboard.manager.run-analysis');
    });

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('demo.capability:system')
        ->name('dashboard.admin');
});
