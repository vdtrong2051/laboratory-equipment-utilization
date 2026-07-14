<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Models\Equipment;
use Illuminate\Support\Carbon;

class UnderutilizedRule implements EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult
    {
        $lastUsedAt = $equipment->last_used_at
            ?? $equipment->usageSessions()->whereNotNull('ended_at')->latest('ended_at')->value('ended_at');

        if ($lastUsedAt === null) {
            $matched = $equipment->created_at?->lte(now()->subDays(60)) ?? false;

            return new RuleEvaluationResult(
                static::class,
                $matched,
                $matched ? 'warning' : 'info',
                $matched ? 'Thiết bị chưa từng được sử dụng trong hơn 60 ngày.' : null,
                [
                    'created_at' => $equipment->created_at?->toISOString(),
                    'threshold_days' => 60,
                ],
            );
        }

        $lastUsedAt = $equipment->last_used_at ?? Carbon::parse($lastUsedAt);
        $idleDays = (int) $lastUsedAt->diffInDays(now());
        $matched = $idleDays > 60;

        return new RuleEvaluationResult(
            static::class,
            $matched,
            $matched ? 'warning' : 'info',
            $matched ? 'Thiết bị không được sử dụng trong hơn 60 ngày.' : null,
            [
                'last_used_at' => $lastUsedAt->toISOString(),
                'idle_days' => $idleDays,
                'threshold_days' => 60,
            ],
        );
    }
}
