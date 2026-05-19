<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCashFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_generates_income_cash_flow(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Rizky',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'is_member' => false,
        ]);

        $service = Service::create([
            'name' => 'Cuci Sepatu',
            'price' => 50000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'services' => [
                ['service_id' => $service->id, 'price' => 50000, 'quantity' => 1, 'description' => 'aturan'],
            ],
            'total_price' => 50000,
            'discount' => 0,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDays(2)->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'income',
            'title' => "Order #{$order->id} payment received",
            'amount' => 50000,
            'created_by' => $user->id,
        ]);
    }

    public function test_order_cancellation_generates_expense_cash_flow(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Aisyah',
            'phone' => '089876543210',
            'address' => 'Jl. Contoh No. 2',
            'is_member' => true,
        ]);

        $service = Service::create([
            'name' => 'Repaint',
            'price' => 120000,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'services' => [
                ['service_id' => $service->id, 'price' => 120000, 'quantity' => 1, 'description' => 'cat ulang'],
            ],
            'total_price' => 120000,
            'discount' => 0,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDays(3)->toDateString(),
            'created_by' => $user->id,
        ]);

        $order->status = 'cancelled';
        $order->save();

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'expense',
            'title' => "Order #{$order->id} cancelled",
            'amount' => 120000,
            'created_by' => $user->id,
        ]);
    }
}
