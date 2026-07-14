<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageSession>
 */
class UsageSessionFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-7 days', 'now');
        $endedAt = (clone $startedAt)->modify('+90 minutes');

        return [
            'booking_id' => Booking::factory(),
            'equipment_id' => Equipment::factory(),
            'user_id' => User::factory(),
            'checked_in_by' => null,
            'completed_by' => null,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'status' => BookingStatus::Completed,
            'notes' => null,
        ];
    }
}
