<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected $model = User::class;
    // protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin', 'volunteer', 'beneficiary'];
        
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement($roles),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
    }


    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
            ];
        });
    }

    public function volunteer()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'volunteer',
            ];
        });
    }

    public function beneficiary()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'beneficiary',
            ];
        });
    }



    /**
     * Indicate that the model's email address should be unverified.
     */
    // public function unverified(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'email_verified_at' => null,
    //     ]);
    // }
}
