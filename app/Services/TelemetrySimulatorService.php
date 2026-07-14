<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Models\ActivitySignal;
use App\Models\PowerEvent;
use App\Models\UsageSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TelemetrySimulatorService
{
    public function simulateForUsageSession(UsageSession $usageSession, int $samples = 12): array
    {
        if (! in_array($usageSession->status, [BookingStatus::CheckedIn, BookingStatus::Overdue, BookingStatus::Completed], true)) {
            throw ValidationException::withMessages([
                'usage_session' => 'Chỉ phiên CHECKED_IN, OVERDUE hoặc COMPLETED mới có thể sinh telemetry mô phỏng.',
            ]);
        }

        $samples = max(3, min($samples, 60));
        $usageSession->loadMissing(['equipment', 'user']);

        return DB::transaction(function () use ($usageSession, $samples): array {
            $startedAt = $usageSession->started_at ?? now();
            $endedAt = $usageSession->ended_at ?? now();
            $gatewayId = 'SIM-GW-'.$usageSession->equipment->laboratory;

            $powerOn = PowerEvent::query()->create([
                'equipment_id' => $usageSession->equipment_id,
                'event_type' => PowerEventType::PowerOn,
                'source' => 'simulated',
                'gateway_id' => $gatewayId,
                'recorded_by' => $usageSession->checked_in_by,
                'recorded_at' => $startedAt->copy()->subMinutes(2),
                'raw_payload' => [
                    'usage_session_id' => $usageSession->id,
                    'simulator' => 'phase_6',
                    'event' => 'power_on_before_session',
                ],
            ]);

            $signals = collect($this->sampleTimestamps($startedAt, $endedAt, $samples))
                ->map(function (Carbon $recordedAt, int $index) use ($usageSession, $gatewayId, $samples): ActivitySignal {
                    $signalValue = $this->simulatedCurrentValue($index, $samples);

                    return ActivitySignal::query()->create([
                        'equipment_id' => $usageSession->equipment_id,
                        'usage_session_id' => $usageSession->id,
                        'signal_type' => 'current_sensor',
                        'signal_value' => $signalValue,
                        'unit' => 'A',
                        'is_active' => $signalValue >= 0.8,
                        'source' => 'simulated',
                        'gateway_id' => $gatewayId,
                        'recorded_by' => $usageSession->checked_in_by,
                        'recorded_at' => $recordedAt,
                        'raw_payload' => [
                            'usage_session_id' => $usageSession->id,
                            'simulator' => 'phase_6',
                            'sample_index' => $index + 1,
                        ],
                    ]);
                });

            $powerOff = null;

            if ($usageSession->status === BookingStatus::Completed && $usageSession->ended_at !== null) {
                $powerOff = PowerEvent::query()->create([
                    'equipment_id' => $usageSession->equipment_id,
                    'event_type' => PowerEventType::PowerOff,
                    'source' => 'simulated',
                    'gateway_id' => $gatewayId,
                    'recorded_by' => $usageSession->completed_by,
                    'recorded_at' => $usageSession->ended_at->copy()->addMinutes(1),
                    'raw_payload' => [
                        'usage_session_id' => $usageSession->id,
                        'simulator' => 'phase_6',
                        'event' => 'power_off_after_session',
                    ],
                ]);
            }

            return [
                'power_events_created' => $powerOff === null ? 1 : 2,
                'activity_signals_created' => $signals->count(),
                'power_on_id' => $powerOn->id,
                'power_off_id' => $powerOff?->id,
                'first_signal_at' => $signals->first()?->recorded_at,
                'last_signal_at' => $signals->last()?->recorded_at,
            ];
        });
    }

    /**
     * @return array<int, Carbon>
     */
    private function sampleTimestamps(Carbon $startedAt, Carbon $endedAt, int $samples): array
    {
        $durationSeconds = max($startedAt->diffInSeconds($endedAt), $samples);
        $stepSeconds = max(1, intdiv($durationSeconds, max(1, $samples - 1)));

        return collect(range(0, $samples - 1))
            ->map(fn (int $index): Carbon => $startedAt->copy()->addSeconds($index * $stepSeconds))
            ->all();
    }

    private function simulatedCurrentValue(int $index, int $samples): float
    {
        $warmupOrCooldown = $index === 0 || $index === $samples - 1;

        if ($warmupOrCooldown) {
            return fake()->randomFloat(4, 0.05, 0.4);
        }

        if ($index % 5 === 0) {
            return fake()->randomFloat(4, 0.1, 0.6);
        }

        return fake()->randomFloat(4, 0.9, 4.5);
    }
}
