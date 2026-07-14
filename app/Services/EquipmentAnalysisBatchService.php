<?php

namespace App\Services;

use App\Models\AnalysisRun;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Carbon;

class EquipmentAnalysisBatchService
{
    public function __construct(
        private readonly OperationalStatusService $operationalStatusService,
        private readonly UsageMetricService $usageMetricService,
        private readonly RuleEvaluationService $ruleEvaluationService,
    ) {}

    public function run(Carbon $periodStart, Carbon $periodEnd, ?User $triggeredBy = null, string $triggerSource = 'manual'): array
    {
        $summary = [
            'processed_equipment' => 0,
            'operational_status_evaluated' => 0,
            'usage_metrics_calculated' => 0,
            'rule_sets_evaluated' => 0,
            'matched_rules' => 0,
        ];

        $analysisRun = AnalysisRun::query()->create([
            'status' => 'running',
            'trigger_source' => $triggerSource,
            'triggered_by' => $triggeredBy?->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'started_at' => now(),
        ]);

        try {
            Equipment::query()
                ->orderBy('id')
                ->chunkById(50, function ($equipments) use ($periodStart, $periodEnd, &$summary): void {
                    foreach ($equipments as $equipment) {
                        $this->operationalStatusService->evaluateAndPersist($equipment);
                        $this->usageMetricService->calculateForEquipment($equipment->refresh(), $periodStart, $periodEnd);
                        $ruleResults = $this->ruleEvaluationService->evaluateAndPersist($equipment->refresh());

                        $summary['processed_equipment']++;
                        $summary['operational_status_evaluated']++;
                        $summary['usage_metrics_calculated']++;
                        $summary['rule_sets_evaluated']++;
                        $summary['matched_rules'] += $ruleResults->where('matched', true)->count();
                    }
                });

            $analysisRun->update([
                ...$summary,
                'status' => 'completed',
                'summary' => $summary,
                'finished_at' => now(),
            ]);

            return [
                ...$summary,
                'analysis_run_id' => $analysisRun->id,
            ];
        } catch (\Throwable $throwable) {
            $analysisRun->update([
                ...$summary,
                'status' => 'failed',
                'summary' => $summary,
                'error_message' => $throwable->getMessage(),
                'finished_at' => now(),
            ]);

            throw $throwable;
        }
    }
}
