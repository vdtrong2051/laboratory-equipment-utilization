<?php

namespace Database\Factories;

use App\Enums\PowerEventType;
use App\Models\Equipment;
use App\Models\PowerEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PowerEvent>
 */
class PowerEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'event_type' => fake()->randomElement(PowerEventType::cases()),
            'source' => 'simulated',
            'gateway_id' => 'SIM-GW-01',
            'recorded_by' => null,
            'recorded_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'raw_payload' => ['source' => 'factory'],
        ];
    }
}
