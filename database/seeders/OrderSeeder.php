<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $customers = Customer::all();
        $services = Service::all();
        $creator = User::firstWhere('email', 'superadmin@gmail.com') ?? User::first();

        if ($customers->isEmpty() || $services->isEmpty() || ! $creator) {
            return;
        }

        $statuses = ['pending', 'in_progress', 'ready_for_pickup', 'completed', 'cancelled'];

        foreach ($customers as $customer) {
            foreach (range(1, $faker->numberBetween(1, 3)) as $orderIndex) {
                $orderServices = [];
                $totalPrice = 0;

                foreach ($services->random($faker->numberBetween(1, min(3, $services->count()))) as $service) {
                    $quantity = $faker->numberBetween(1, 3);
                    $price = $service->price * $quantity;

                    $orderServices[] = [
                        'service_id' => $service->id,
                        'price' => $price,
                        'quantity' => $quantity,
                        'description' => $faker->sentence(6),
                    ];

                    $totalPrice += $price;
                }

                $status = $faker->randomElement($statuses);
                $estimatedFinishedDate = Carbon::now()->addDays($faker->numberBetween(1, 14));
                $finishedDate = $status === 'completed'
                    ? (clone $estimatedFinishedDate)->addDays($faker->numberBetween(0, 3))
                    : null;

                Order::create([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_address' => $customer->address,
                    'services' => $orderServices,
                    'total_price' => $totalPrice,
                    'status' => $status,
                    'estimated_finished_date' => $estimatedFinishedDate,
                    'finished_date' => $finishedDate,
                    'created_by' => $creator->id,
                ]);
            }
        }
    }
}
