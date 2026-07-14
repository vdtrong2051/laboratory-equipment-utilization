<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-7 days', '+7 days');
        $end = (clone $start)->modify('+2 hours');

        return [
            'equipment_id' => Equipment::factory(),
            'user_id' => User::factory(),
            'created_by' => null,
            'start_time' => $start,
            'end_time' => $end,
            'status' => BookingStatus::Booked,
            'purpose' => fake()->sentence(),
        ];
    }
}
