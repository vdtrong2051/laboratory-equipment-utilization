<?php

namespace App\Rules\Equipment;

use App\DTOs\RuleEvaluationResult;
use App\Enums\BookingStatus;
use App\Models\Equipment;

class NoShowRule implements EquipmentRule
{
    public function evaluate(Equipment $equipment): RuleEvaluationResult
    {
        $booking = $equipment->bookings()
            ->where('status', BookingStatus::Booked)
            ->where('end_time', '<', now())
            ->whereDoesntHave('usageSession')
            ->oldest('end_time')
            ->first();

        $matched = $booking !== null;

        return new RuleEvaluationResult(
            static::class,
            $matched,
            $matched ? 'warning' : 'info',
            $matched ? 'Có booking đã quá thời gian nhưng người dùng không check-in.' : null,
            [
                'booking_id' => $booking?->id,
                'booking_start_time' => $booking?->start_time?->toISOString(),
                'booking_end_time' => $booking?->end_time?->toISOString(),
            ],
        );
    }
}
