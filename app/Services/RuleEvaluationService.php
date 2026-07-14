<?php

namespace App\Services;

use App\DTOs\RuleEvaluationResult;
use App\Enums\AnalysisStatus;
use App\Models\AbnormalPattern;
use App\Models\Equipment;
use App\Rules\Equipment\CapacityPressureRule;
use App\Rules\Equipment\EquipmentRule;
use App\Rules\Equipment\IdleWhilePoweredRule;
use App\Rules\Equipment\NoShowRule;
use App\Rules\Equipment\OverdueRule;
use App\Rules\Equipment\UnderutilizedRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RuleEvaluationService
{
    /**
     * @param  array<int, EquipmentRule>  $rules
     */
    public function __construct(
        private readonly array $rules = [],
    ) {}

    /**
     * @return Collection<int, RuleEvaluationResult>
     */
    public function evaluateAndPersist(Equipment $equipment): Collection
    {
        $rules = $this->rules ?: $this->defaultRules();

        return DB::transaction(function () use ($equipment, $rules): Collection {
            $results = collect($rules)
                ->map(fn (EquipmentRule $rule): RuleEvaluationResult => $rule->evaluate($equipment));

            $results
                ->filter(fn (RuleEvaluationResult $result): bool => $result->matched)
                ->each(fn (RuleEvaluationResult $result): AbnormalPattern => $this->persistResult($equipment, $result));

            $equipment->update([
                'current_analysis_status' => $this->analysisStatusFromResults($results),
            ]);

            return $results;
        });
    }

    /**
     * @return array<int, EquipmentRule>
     */
    private function defaultRules(): array
    {
        return [
            app(OverdueRule::class),
            app(NoShowRule::class),
            app(IdleWhilePoweredRule::class),
            app(CapacityPressureRule::class),
            app(UnderutilizedRule::class),
        ];
    }

    private function persistResult(Equipment $equipment, RuleEvaluationResult $result): AbnormalPattern
    {
        return AbnormalPattern::query()->updateOrCreate(
            [
                'equipment_id' => $equipment->id,
                'rule_name' => $result->ruleName,
                'status' => 'open',
            ],
            [
                'usage_session_id' => $result->evidence['usage_session_id'] ?? null,
                'severity' => $result->severity,
                'message' => $result->message,
                'evidence' => $result->evidence,
                'detected_at' => now(),
            ],
        );
    }

    /**
     * @param  Collection<int, RuleEvaluationResult>  $results
     */
    private function analysisStatusFromResults(Collection $results): AnalysisStatus
    {
        if ($results->first(fn (RuleEvaluationResult $result): bool => $result->matched && $result->ruleName === IdleWhilePoweredRule::class)) {
            return AnalysisStatus::IdleWhilePowered;
        }

        if ($results->first(fn (RuleEvaluationResult $result): bool => $result->matched && $result->ruleName === CapacityPressureRule::class)) {
            return AnalysisStatus::CapacityPressure;
        }

        if ($results->first(fn (RuleEvaluationResult $result): bool => $result->matched && $result->ruleName === UnderutilizedRule::class)) {
            return AnalysisStatus::Underutilized;
        }

        return AnalysisStatus::Normal;
    }
}
