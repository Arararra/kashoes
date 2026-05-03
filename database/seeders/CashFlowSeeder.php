<?php

namespace Database\Seeders;

use App\Models\CashFlow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CashFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $creator = User::firstWhere('email', 'superadmin@gmail.com') ?? User::first();

        if (! $creator) {
            return;
        }

        foreach (range(1, 8) as $index) {
            $type = $faker->randomElement(['income', 'expense']);

            CashFlow::create([
                'date' => Carbon::now()->subDays($faker->numberBetween(0, 30)),
                'type' => $type,
                'title' => ($type === 'income' ? 'Pendapatan' : 'Pengeluaran').' '.$faker->words(3, true),
                'description' => $faker->sentence(8),
                'amount' => $faker->randomFloat(2, 10000, 150000),
                'created_by' => $creator->id,
            ]);
        }
    }
}
