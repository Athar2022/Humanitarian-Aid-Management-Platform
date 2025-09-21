<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@humanitarian.aid',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '777-777-777',
            'address' => 'Admin Address, City, Country',
        ]);

        User::factory()->count(5)->volunteer()->create();

        User::factory()->count(20)->beneficiary()->create();

        User::factory()->count(10)->create();
        
    }
}
