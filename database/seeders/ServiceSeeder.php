<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Shoe Cleaning', 'price' => 10000.00],
            ['name' => 'Shoe Polishing', 'price' => 15000.00],
            ['name' => 'Shoe Repair', 'price' => 20000.00],
            ['name' => 'Shoe Customization', 'price' => 25000.00],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
