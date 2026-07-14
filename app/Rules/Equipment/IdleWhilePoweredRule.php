<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Enums\PowerEventType;
use App\Models\Equipment;
use Illuminate\Support\Carbon;

class IdleWhilePoweredRule implements EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult
    {
        $latestPowerEvent = $equipment->powerEvents()->latest('recorded_at')->first();

        if ($latestPowerEvent?->event_type !== PowerEventType::PowerOn) {
            return new RuleEvaluationResult(static::class, false);
        }

        $latestActiveSignalAt = $equipment->activitySignals()
            ->where('recorded_at', '>=', $latestPowerEvent->recorded_at)
            ->where('is_active', true)
            ->latest('recorded_at')
            ->value('recorded_at');

        $latestActiveSignalAt = $latestActiveSignalAt === null ? null : Carbon::parse($latestActiveSignalAt);

        $idleStartedAt = $latestActiveSignalAt === null
            ? $latestPowerEvent->recorded_at
            : $latestActiveSignalAt;

        $idleMinutes = (int) $idleStartedAt->diffInMinutes(now());
        $matched = $idleMinutes > 30;

        return new RuleEvaluationResult(
            static::class,
            $matched,
            $matched ? 'warning' : 'info',
            $matched ? 'Thiết bị đang bật nhưng không có hoạt động trong hơn 30 phút.' : null,
            [
                'power_event_id' => $latestPowerEvent->id,
                'power_on_at' => $latestPowerEvent->recorded_at?->toISOString(),
                'latest_active_signal_at' => $latestActiveSignalAt?->toISOString(),
                'idle_minutes' => $idleMinutes,
                'threshold_minutes' => 30,
            ],
        );
    }
}
