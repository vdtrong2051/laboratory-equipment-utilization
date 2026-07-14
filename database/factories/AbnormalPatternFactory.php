<?php

namespace Database\Factories;

use App\Models\AbnormalPattern;
use App\Models\Equipment;
use App\Models\UsageSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbnormalPattern>
 */
class AbnormalPatternFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'usage_session_id' => UsageSession::factory(),
            'rule_name' => 'DemoRule',
            'severity' => 'info',
            'status' => 'open',
            'message' => 'Demo abnormal pattern.',
            'evidence' => ['source' => 'factory'],
            'detected_at' => now(),
        ];
    }
}
