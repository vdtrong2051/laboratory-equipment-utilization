<?php

namespace App\Rules\Equipment;

use App\DTOs\OperationalStatusDecision;
use App\Enums\OperationalStatus;
use App\Enums\PowerEventType;
use App\Models\ActivitySignal;
use App\Models\Equipment;
use App\Models\PowerEvent;

class OperationalStatusRule
{
    public function classify(Equipment $equipment): OperationalStatusDecision
    {
        $latestPowerEvent = $equipment->powerEvents()
            ->latest('recorded_at')
            ->first();

        if (! $latestPowerEvent instanceof PowerEvent) {
            return new OperationalStatusDecision(
                OperationalStatus::Off,
                'Chưa có power event nên backend mặc định xem thiết bị đang OFF.',
            );
        }

        if ($latestPowerEvent->event_type === PowerEventType::PowerOff) {
            return new OperationalStatusDecision(
                OperationalStatus::Off,
                'Power event mới nhất là POWER_OFF.',
                [
                    'power_event_id' => $latestPowerEvent->id,
                    'power_event_recorded_at' => $latestPowerEvent->recorded_at?->toISOString(),
                ],
            );
        }

        $latestSignal = $equipment->activitySignals()
            ->where('recorded_at', '>=', $latestPowerEvent->recorded_at)
            ->latest('recorded_at')
            ->first();

        if (! $latestSignal instanceof ActivitySignal) {
            return new OperationalStatusDecision(
                OperationalStatus::PoweredIdle,
                'Thiết bị đã POWER_ON nhưng chưa có activity signal sau thời điểm bật.',
                [
                    'power_event_id' => $latestPowerEvent->id,
                    'power_event_recorded_at' => $latestPowerEvent->recorded_at?->toISOString(),
                ],
            );
        }

        if ($latestSignal->is_active === true) {
            return new OperationalStatusDecision(
                OperationalStatus::Active,
                'Power đang bật và activity signal mới nhất đang active.',
                [
                    'power_event_id' => $latestPowerEvent->id,
                    'activity_signal_id' => $latestSignal->id,
                    'activity_signal_recorded_at' => $latestSignal->recorded_at?->toISOString(),
                    'signal_value' => $latestSignal->signal_value,
                ],
            );
        }

        return new OperationalStatusDecision(
            OperationalStatus::PoweredIdle,
            'Power đang bật nhưng activity signal mới nhất không active.',
            [
                'power_event_id' => $latestPowerEvent->id,
                'activity_signal_id' => $latestSignal->id,
                'activity_signal_recorded_at' => $latestSignal->recorded_at?->toISOString(),
                'signal_value' => $latestSignal->signal_value,
            ],
        );
    }
}
