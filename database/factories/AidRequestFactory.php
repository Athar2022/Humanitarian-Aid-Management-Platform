<?php

namespace Database\Factories;

use App\Models\AidRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AidRequest>
 */
class AidRequestFactory extends Factory
{
    protected $model = AidRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'beneficiary_id' => User::where('role', 'beneficiary')->inRandomOrder()->first()->id,
            'type' => $this->faker->randomElement(['food', 'clothing', 'medical', 'shelter', 'other']),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'denied']),
            'document_url' => $this->faker->optional()->imageUrl(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
