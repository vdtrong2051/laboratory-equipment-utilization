<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PowerEventType;
use App\Models\AbnormalPattern;
use App\Models\ActivitySignal;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\UsageMetric;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $researcher = User::query()->where('email', 'researcher.an@lab.local')->firstOrFail();
        $labStaff = User::query()->where('email', 'staff.a@lab.local')->firstOrFail();
        $manager = User::query()->where('email', 'manager@lab.local')->firstOrFail();

        $microscope = Equipment::query()->where('equipment_code', 'MICRO-001')->firstOrFail();
        $centrifuge = Equipment::query()->where('equipment_code', 'CEN-001')->firstOrFail();

        $startedAt = now()->subDays(2)->setTime(9, 0);
        $endedAt = now()->subDays(2)->setTime(10, 45);

        $booking = Booking::query()->create([
            'equipment_id' => $microscope->id,
            'user_id' => $researcher->id,
            'created_by' => $researcher->id,
            'start_time' => $startedAt,
            'end_time' => $startedAt->copy()->addMinutes(120),
            'status' => BookingStatus::Completed,
            'purpose' => 'Quan sát mẫu mô học demo.',
        ]);

        $session = UsageSession::query()->create([
            'booking_id' => $booking->id,
            'equipment_id' => $microscope->id,
            'user_id' => $researcher->id,
            'checked_in_by' => $labStaff->id,
            'completed_by' => $labStaff->id,
            'started_at' => $startedAt->copy()->addMinutes(5),
            'ended_at' => $endedAt,
            'status' => BookingStatus::Completed,
            'notes' => 'Phiên sử dụng demo cho kiểm tra quan hệ dữ liệu.',
        ]);

        PowerEvent::query()->create([
            'equipment_id' => $microscope->id,
            'event_type' => PowerEventType::PowerOn,
            'source' => 'simulated',
            'gateway_id' => 'SIM-GW-LAB-A',
            'recorded_by' => $labStaff->id,
            'recorded_at' => $startedAt->copy()->addMinutes(4),
            'raw_payload' => ['scenario' => 'normal_usage'],
        ]);

        PowerEvent::query()->create([
            'equipment_id' => $microscope->id,
            'event_type' => PowerEventType::PowerOff,
            'source' => 'simulated',
            'gateway_id' => 'SIM-GW-LAB-A',
            'recorded_by' => $labStaff->id,
            'recorded_at' => $endedAt->copy()->addMinutes(2),
            'raw_payload' => ['scenario' => 'normal_usage'],
        ]);

        foreach ([15, 45, 75] as $minuteOffset) {
            ActivitySignal::query()->create([
                'equipment_id' => $microscope->id,
                'usage_session_id' => $session->id,
                'signal_type' => 'manual_activity',
                'signal_value' => 1,
                'unit' => 'event',
                'is_active' => true,
                'source' => 'simulated',
                'gateway_id' => 'SIM-GW-LAB-A',
                'recorded_by' => $labStaff->id,
                'recorded_at' => $startedAt->copy()->addMinutes($minuteOffset),
                'raw_payload' => ['scenario' => 'normal_usage'],
            ]);
        }

        UsageMetric::query()->create([
            'equipment_id' => $microscope->id,
            'period_start' => now()->subDays(30)->startOfDay(),
            'period_end' => now()->endOfDay(),
            'total_booked_minutes' => 120,
            'total_powered_minutes' => 123,
            'total_active_minutes' => 90,
            'total_idle_minutes' => 33,
            'booking_utilization_rate' => 4.17,
            'actual_utilization_rate' => 3.13,
            'powered_idle_rate' => 26.83,
            'calculated_at' => now(),
        ]);

        AbnormalPattern::query()->create([
            'equipment_id' => $centrifuge->id,
            'usage_session_id' => null,
            'rule_name' => 'DemoDataQualityRule',
            'severity' => 'info',
            'status' => 'open',
            'message' => 'Cảnh báo demo để kiểm tra luồng abnormal pattern.',
            'evidence' => [
                'note' => 'Dữ liệu mẫu Phase 1, chưa phải rule evaluation thật.',
            ],
            'detected_at' => now(),
            'reviewed_by' => $manager->id,
            'reviewed_at' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);

        $microscope->forceFill([
            'last_used_at' => $endedAt,
            'utilization_rate' => 3.13,
        ])->save();
    }
}
