<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'phone' => '081234567890',
                'address' => 'Head Office',
                'password' => Hash::make('admin123'),
            ]
        );

        $superAdmin->assignRole('super_admin');

        // Create 5 customer users
        foreach (range(1, 5) as $i) {
            $user = User::create([
                'name' => $faker->name(),
                'email' => "customer{$i}@gmail.com",
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('customer');
        }
    }
}