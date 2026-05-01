<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        foreach (range(1, 10) as $index) {
            Customer::create([
                'name' => $faker->name(),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'is_member' => $faker->boolean(30),
            ]);
        }
    }
}
