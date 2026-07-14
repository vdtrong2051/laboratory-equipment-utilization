<?php

namespace Database\Factories;

use App\Models\AnalysisRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisRun>
 */
class AnalysisRunFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = now()->subMinutes(5);

        return [
            'status' => 'completed',
            'trigger_source' => 'manual',
            'triggered_by' => User::factory(),
            'period_start' => now()->subDays(30)->startOfDay(),
            'period_end' => now()->endOfDay(),
            'processed_equipment' => 0,
            'operational_status_evaluated' => 0,
            'usage_metrics_calculated' => 0,
            'rule_sets_evaluated' => 0,
            'matched_rules' => 0,
            'summary' => ['source' => 'factory'],
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addMinutes(1),
        ];
    }
}
