<?php

use App\Services\EquipmentAnalysisBatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('equipment:analyze {--days=30 : Number of days included in the analysis window}', function () {
    $days = max(1, (int) $this->option('days'));
    $periodStart = now()->subDays($days)->startOfDay();
    $periodEnd = now()->endOfDay();

    $summary = app(EquipmentAnalysisBatchService::class)->run($periodStart, $periodEnd, triggerSource: 'artisan');

    $this->info('Equipment analysis completed.');
    $this->table(['Metric', 'Value'], collect($summary)->map(fn ($value, $key) => [$key, $value])->all());

    return self::SUCCESS;
})->purpose('Run operational status, usage metrics, and rule evaluation for all equipment');
