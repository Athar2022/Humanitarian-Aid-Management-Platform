<?php

namespace Database\Factories;

use App\Models\Distribution;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Distribution>
 */
class DistributionFactory extends Factory
{
    protected $model = Distribution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'volunteer_id' => User::where('role', 'volunteer')->inRandomOrder()->first()->id,
            'beneficiary_id' => User::where('role', 'beneficiary')->inRandomOrder()->first()->id,
            'donation_id' => Donation::inRandomOrder()->first()->id,
            'delivery_status' => $this->faker->randomElement(['assigned', 'in_progress', 'delivered']),
            'proof_file' => $this->faker->optional()->imageUrl(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
