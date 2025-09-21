<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['food', 'clothing', 'medical', 'shelter', 'other'];
        $statuses = ['pending', 'approved', 'distributed'];
        
        return [
            'donor_name' => $this->faker->name(),
            'type' => $this->faker->randomElement($types),
            'quantity' => $this->faker->numberBetween(1, 100),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
