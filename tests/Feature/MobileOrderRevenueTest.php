<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileOrderRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
    }

    public function test_mobile_completion_records_current_revenue_for_an_older_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = $this->pendingOrder($admin);
        $order->forceFill(['created_at' => now()->subMonths(2)])->saveQuietly();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('status', 'completed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
        $this->assertSame(now()->toDateString(), $order->refresh()->finished_date->toDateString());
        $this->assertDatabaseHas('cash_flows', [
            'order_id' => $order->id,
            'type' => 'income',
            'amount' => 50000,
            'created_by' => $admin->id,
        ]);
        $income = CashFlow::where('order_id', $order->id)->sole();
        $this->assertSame(now()->toDateString(), $income->date->toDateString());

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/dashboard/stats')
            ->assertOk()
            ->assertJsonPath('data.revenue.today', 50000)
            ->assertJsonPath('data.revenue.this_month', 50000);
    }

    public function test_repeated_completion_and_forged_total_keep_one_correct_income_record(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = $this->pendingOrder($admin);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertOk();
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertOk();
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['total_price' => 75000])
            ->assertOk();

        $this->assertSame(1, CashFlow::where('order_id', $order->id)->count());
        $this->assertDatabaseHas('cash_flows', [
            'order_id' => $order->id,
            'type' => 'income',
            'amount' => 50000,
        ]);
    }

    public function test_reopening_an_order_removes_income_and_completion_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = $this->pendingOrder($admin);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertOk();
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'ready_for_pickup'])
            ->assertOk();

        $this->assertDatabaseMissing('cash_flows', ['order_id' => $order->id]);
        $this->assertNull($order->refresh()->finished_date);
    }

    public function test_restoring_completed_order_recreates_its_income(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = $this->pendingOrder($admin);
        $order->update(['status' => 'completed']);

        $order->delete();
        $this->assertDatabaseMissing('cash_flows', ['order_id' => $order->id]);

        $order->restore();

        $this->assertDatabaseHas('cash_flows', [
            'order_id' => $order->id,
            'type' => 'income',
            'amount' => 50000,
        ]);
    }

    private function pendingOrder(User $creator): Order
    {
        $customer = Customer::create([
            'name' => 'Rizky',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'is_member' => false,
        ]);

        return Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'services' => [
                ['service_id' => 1, 'price' => 25000, 'quantity' => 2, 'description' => 'Deep Clean'],
            ],
            'total_price' => 50000,
            'discount' => 0,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDays(2)->toDateString(),
            'created_by' => $creator->id,
        ]);
    }
}
