<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Enums\BookingStatus;
use App\Models\Equipment;

class OverdueRule implements EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult
    {
        $usageSession = $equipment->usageSessions()
            ->with('booking')
            ->whereIn('status', [BookingStatus::CheckedIn, BookingStatus::Overdue])
            ->whereNull('ended_at')
            ->whereHas('booking', fn ($query) => $query->where('end_time', '<', now()))
            ->oldest('started_at')
            ->first();

        $matched = $usageSession !== null;

        return new RuleEvaluationResult(
            static::class,
            $matched,
            $matched ? 'critical' : 'info',
            $matched ? 'Có phiên sử dụng đang giữ thiết bị quá thời gian cho phép.' : null,
            [
                'usage_session_id' => $usageSession?->id,
                'booking_id' => $usageSession?->booking?->id,
                'booking_end_time' => $usageSession?->booking?->end_time?->toISOString(),
                'started_at' => $usageSession?->started_at?->toISOString(),
            ],
        );
    }
}
