<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashFlowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }

    public function test_admin_can_record_a_cash_flow_for_the_web_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/cash-flows', [
                'date' => now()->toDateString(),
                'type' => 'expense',
                'title' => 'Pembelian sabun',
                'description' => 'Stok operasional',
                'amount' => 125000,
                'created_by' => User::factory()->create()->id,
            ])
            ->assertCreated()
            ->assertJsonPath('created_by', $admin->id);

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'expense',
            'title' => 'Pembelian sabun',
            'amount' => 125000,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/cash-flows')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Pembelian sabun');
    }

    public function test_customer_cannot_access_cash_flow_api(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/cash-flows')
            ->assertForbidden();
        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/cash-flows', [
                'date' => now()->toDateString(),
                'type' => 'income',
                'title' => 'Tidak boleh',
                'amount' => 10000,
            ])
            ->assertForbidden();

        $this->assertSame(0, CashFlow::count());
    }

    public function test_type_and_positive_amount_are_validated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/cash-flows', [
                'date' => now()->toDateString(),
                'type' => 'other',
                'title' => 'Invalid',
                'amount' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'amount']);
    }

    public function test_order_income_cannot_be_edited_or_deleted_manually(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $customer = Customer::create(['name' => 'Budi']);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'services' => [],
            'total_price' => 50000,
            'discount' => 0,
            'status' => 'completed',
            'estimated_finished_date' => now()->toDateString(),
            'created_by' => $admin->id,
        ]);
        $cashFlow = CashFlow::where('order_id', $order->id)->sole();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/cash-flows/{$cashFlow->id}", ['amount' => 1])
            ->assertForbidden();
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/cash-flows/{$cashFlow->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('cash_flows', [
            'id' => $cashFlow->id,
            'amount' => 50000,
        ]);
    }
}
