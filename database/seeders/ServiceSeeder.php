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
            ['name' => 'Deep Clean', 'price' => 25000.00],
            ['name' => 'White Shoes', 'price' => 35000.00],
            ['name' => 'Fast Clean', 'price' => 50000.00],
            ['name' => 'Girl/Kid Shoes/Sandal', 'price' => 20000.00],
            ['name' => 'Unyellowing', 'price' => 30000.00],
            ['name' => 'Repaint', 'price' => 75000.00],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
