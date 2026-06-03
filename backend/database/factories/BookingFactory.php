<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+2 weeks');

        return [
            'user_id' => User::factory(),
            'service_name' => fake()->randomElement(['Consultation', 'Site visit', 'Follow-up session']),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'status' => 'confirmed',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
