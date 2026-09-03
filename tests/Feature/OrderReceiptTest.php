<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
    }

    public function test_receipt_multiplies_unit_price_by_quantity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $service = $this->service($user);
        $order = $this->order($user, $service, price: 25000, total: 50000);

        $this->actingAs($user)
            ->get(route('orders.receipt', $order))
            ->assertOk()
            ->assertSee('2 × Rp 25.000')
            ->assertSee('Rp 50.000');
    }

    public function test_receipt_keeps_legacy_web_line_prices_compatible(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $service = $this->service($user);
        $order = $this->order($user, $service, price: 50000, total: 50000);

        $this->actingAs($user)
            ->get(route('orders.receipt', $order))
            ->assertOk()
            ->assertSee('2 × Rp 25.000')
            ->assertSee('Rp 50.000');
    }

    private function service(User $user): Service
    {
        return Service::create([
            'name' => 'Deep Clean',
            'price' => 25000,
            'created_by' => $user->id,
        ]);
    }

    private function order(User $user, Service $service, float $price, float $total): Order
    {
        $customer = Customer::create([
            'name' => 'Budi',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh',
            'is_member' => false,
        ]);

        return Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'services' => [[
                'service_id' => $service->id,
                'price' => $price,
                'quantity' => 2,
                'description' => 'Deep Clean',
            ]],
            'total_price' => $total,
            'discount' => 0,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDays(2)->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
