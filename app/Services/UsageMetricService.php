<?php

namespace App\Services;

use App\Enums\PowerEventType;
use App\Models\ActivitySignal;
use App\Models\Equipment;
use App\Models\UsageMetric;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UsageMetricService
{
    public function calculateForEquipment(Equipment $equipment, Carbon $periodStart, Carbon $periodEnd): UsageMetric
    {
        if ($periodEnd->lte($periodStart)) {
            throw new \InvalidArgumentException('periodEnd must be after periodStart.');
        }

        return DB::transaction(function () use ($equipment, $periodStart, $periodEnd): UsageMetric {
            $periodMinutes = max(1, (int) $periodStart->diffInMinutes($periodEnd));
            $totalBookedMinutes = $this->bookedMinutes($equipment, $periodStart, $periodEnd);
            $totalPoweredMinutes = $this->poweredMinutes($equipment, $periodStart, $periodEnd);
            $totalActiveMinutes = min(
                $totalPoweredMinutes,
                $this->activeMinutes($equipment, $periodStart, $periodEnd),
            );
            $totalIdleMinutes = max(0, $totalPoweredMinutes - $totalActiveMinutes);

            $metric = UsageMetric::query()->updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ],
                [
                    'total_booked_minutes' => $totalBookedMinutes,
                    'total_powered_minutes' => $totalPoweredMinutes,
                    'total_active_minutes' => $totalActiveMinutes,
                    'total_idle_minutes' => $totalIdleMinutes,
                    'booking_utilization_rate' => $this->percent($totalBookedMinutes, $periodMinutes),
                    'actual_utilization_rate' => $this->percent($totalActiveMinutes, $periodMinutes),
                    'powered_idle_rate' => $this->percent($totalIdleMinutes, max(1, $totalPoweredMinutes)),
                    'calculated_at' => now(),
                ],
            );

            $equipment->update([
                'utilization_rate' => $metric->actual_utilization_rate,
                'last_used_at' => $equipment->usageSessions()->whereNotNull('ended_at')->latest('ended_at')->value('ended_at') ?? $equipment->last_used_at,
            ]);

            return $metric->refresh();
        });
    }

    private function bookedMinutes(Equipment $equipment, Carbon $periodStart, Carbon $periodEnd): int
    {
        return (int) $equipment->bookings()
            ->where('start_time', '<', $periodEnd)
            ->where('end_time', '>', $periodStart)
            ->get(['start_time', 'end_time'])
            ->sum(fn ($booking): int => $this->overlapMinutes($booking->start_time, $booking->end_time, $periodStart, $periodEnd));
    }

    private function poweredMinutes(Equipment $equipment, Carbon $periodStart, Carbon $periodEnd): int
    {
        $previousPowerEvent = $equipment->powerEvents()
            ->where('recorded_at', '<', $periodStart)
            ->latest('recorded_at')
            ->first();

        $events = $equipment->powerEvents()
            ->whereBetween('recorded_at', [$periodStart, $periodEnd])
            ->orderBy('recorded_at')
            ->get();

        $isPowered = $previousPowerEvent?->event_type === PowerEventType::PowerOn;
        $poweredFrom = $isPowered ? $periodStart->copy() : null;
        $minutes = 0;

        foreach ($events as $event) {
            if ($event->event_type === PowerEventType::PowerOn && ! $isPowered) {
                $isPowered = true;
                $poweredFrom = $event->recorded_at->copy();
            }

            if ($event->event_type === PowerEventType::PowerOff && $isPowered && $poweredFrom !== null) {
                $minutes += $this->overlapMinutes($poweredFrom, $event->recorded_at, $periodStart, $periodEnd);
                $isPowered = false;
                $poweredFrom = null;
            }
        }

        if ($isPowered && $poweredFrom !== null) {
            $minutes += $this->overlapMinutes($poweredFrom, $periodEnd, $periodStart, $periodEnd);
        }

        return max(0, $minutes);
    }

    private function activeMinutes(Equipment $equipment, Carbon $periodStart, Carbon $periodEnd): int
    {
        $signals = $equipment->activitySignals()
            ->whereBetween('recorded_at', [$periodStart, $periodEnd])
            ->orderBy('recorded_at')
            ->get();

        if ($signals->isEmpty()) {
            return 0;
        }

        return (int) $signals
            ->values()
            ->sum(function (ActivitySignal $signal, int $index) use ($signals, $periodEnd): int {
                if (! $signal->is_active) {
                    return 0;
                }

                $nextSignal = $signals->get($index + 1);
                $intervalEnd = $nextSignal?->recorded_at ?? $periodEnd;

                return max(0, min(15, (int) $signal->recorded_at->diffInMinutes($intervalEnd)));
            });
    }

    private function overlapMinutes(Carbon $start, Carbon $end, Carbon $periodStart, Carbon $periodEnd): int
    {
        $overlapStart = $start->greaterThan($periodStart) ? $start : $periodStart;
        $overlapEnd = $end->lessThan($periodEnd) ? $end : $periodEnd;

        if ($overlapEnd->lte($overlapStart)) {
            return 0;
        }

        return (int) $overlapStart->diffInMinutes($overlapEnd);
    }

    private function percent(int $part, int $whole): float
    {
        return round(($part / max(1, $whole)) * 100, 2);
    }
}
