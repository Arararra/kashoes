<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'admin']);
    }

    public function test_customer_order_ignores_spoofed_owner_and_prices(): void
    {
        $user = User::factory()->create(['name' => 'Pemilik Asli']);
        $user->assignRole('customer');
        $ownProfile = Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => '081111111111',
            'address' => 'Alamat asli',
        ]);
        $victim = Customer::create(['name' => 'Korban', 'phone' => '082222222222']);
        $service = Service::create(['name' => 'Deep Clean', 'price' => 25000]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders', [
            'customer_id' => $victim->id,
            'customer_name' => 'Nama palsu',
            'services' => [[
                'service_id' => $service->id,
                'price' => 1,
                'quantity' => 2,
            ]],
            'total_price' => 1,
            'discount' => 49999,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDay()->toDateString(),
            'created_by' => User::factory()->create()->id,
        ])->assertCreated();

        $response
            ->assertJsonPath('customer_id', $ownProfile->id)
            ->assertJsonPath('customer_name', 'Pemilik Asli')
            ->assertJsonPath('services.0.price', 25000)
            ->assertJsonPath('services.0.quantity', 2)
            ->assertJsonPath('total_price', '50000.00')
            ->assertJsonPath('discount', '0.00')
            ->assertJsonPath('created_by', $user->id);
    }

    public function test_customer_cannot_read_or_change_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('customer');
        $ownerProfile = Customer::create(['user_id' => $owner->id, 'name' => 'Owner']);
        $attacker = User::factory()->create();
        $attacker->assignRole('customer');
        Customer::create(['user_id' => $attacker->id, 'name' => 'Attacker']);
        $order = $this->order($ownerProfile, $owner);

        $this->actingAs($attacker, 'sanctum')
            ->getJson("/api/orders/{$order->id}")
            ->assertForbidden();
        $this->actingAs($attacker, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertForbidden();
    }

    public function test_customer_can_only_update_coordinates_on_own_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');
        $profile = Customer::create(['user_id' => $user->id, 'name' => 'Customer']);
        $order = $this->order($profile, $user);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", ['status' => 'completed'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/orders/{$order->id}", [
                'latitude' => -6.2,
                'longitude' => 106.8,
            ])
            ->assertOk()
            ->assertJsonPath('latitude', '-6.20000000');
    }

    private function order(Customer $customer, User $creator): Order
    {
        return Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'services' => [],
            'total_price' => 0,
            'discount' => 0,
            'status' => 'pending',
            'estimated_finished_date' => now()->addDay()->toDateString(),
            'created_by' => $creator->id,
        ]);
    }
}
