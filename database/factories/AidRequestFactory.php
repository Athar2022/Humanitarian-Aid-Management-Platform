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
         $types = ['food', 'clothing', 'medical', 'shelter', 'other'];
        $statuses = ['pending', 'approved', 'denied'];
        
        return [
            'beneficiary_id' => User::where('role', 'beneficiary')->inRandomOrder()->first()->id,
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement($statuses),
            'document_url' => $this->faker->optional()->url(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
