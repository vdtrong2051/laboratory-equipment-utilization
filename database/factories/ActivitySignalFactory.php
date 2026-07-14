<?php

namespace Database\Factories;

use App\Models\ActivitySignal;
use App\Models\Equipment;
use App\Models\UsageSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivitySignal>
 */
class ActivitySignalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'usage_session_id' => UsageSession::factory(),
            'signal_type' => 'current_sensor',
            'signal_value' => fake()->randomFloat(4, 0, 5),
            'unit' => 'A',
            'is_active' => fake()->boolean(80),
            'source' => 'simulated',
            'gateway_id' => 'SIM-GW-01',
            'recorded_by' => null,
            'recorded_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'raw_payload' => ['source' => 'factory'],
        ];
    }
}
