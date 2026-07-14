<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_code' => $this->faker->unique()->bothify('EQ-###'),
            'name' => $this->faker->words(3, true),
            'type' => 'demo',
            'laboratory' => 'Demo Lab',
            'usage_mode' => UsageMode::OnSite,
            'allowed_usage_duration_minutes' => 120,
            'current_operational_status' => OperationalStatus::Off,
            'current_analysis_status' => AnalysisStatus::Normal,
            'utilization_rate' => 0,
        ];
    }
}
