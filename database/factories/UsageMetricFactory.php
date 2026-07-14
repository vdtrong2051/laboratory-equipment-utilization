<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\UsageMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageMetric>
 */
class UsageMetricFactory extends Factory
{
    public function definition(): array
    {
        $periodStart = now()->subDays(30)->startOfDay();
        $periodEnd = now()->endOfDay();

        return [
            'equipment_id' => Equipment::factory(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_booked_minutes' => 0,
            'total_powered_minutes' => 0,
            'total_active_minutes' => 0,
            'total_idle_minutes' => 0,
            'booking_utilization_rate' => 0,
            'actual_utilization_rate' => 0,
            'powered_idle_rate' => 0,
            'calculated_at' => now(),
        ];
    }
}
