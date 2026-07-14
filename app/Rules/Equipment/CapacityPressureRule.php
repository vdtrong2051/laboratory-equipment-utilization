<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Models\Equipment;

class CapacityPressureRule implements EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult
    {
        $utilizationRate = (float) ($equipment->utilization_rate ?? 0);
        $matched = $utilizationRate > 85;

        return new RuleEvaluationResult(
            static::class,
            $matched,
            $matched ? 'warning' : 'info',
            $matched ? 'Tỷ lệ khai thác thiết bị vượt 85%.' : null,
            [
                'utilization_rate' => $utilizationRate,
                'threshold_percent' => 85,
            ],
        );
    }
}
