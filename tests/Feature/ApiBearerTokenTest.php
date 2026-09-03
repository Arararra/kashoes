<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiBearerTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'super_admin']);
    }

    public function test_api_endpoints_require_valid_bearer_token(): void
    {
        $routes = [
            '/api/cash-flows',
            '/api/customers',
            '/api/orders',
            '/api/services',
            '/api/users',
        ];

        foreach ($routes as $route) {
            $this->getJson($route)
                ->assertUnauthorized();

            $this->getJson($route, ['Authorization' => 'Bearer invalid-token'])
                ->assertUnauthorized();
        }
    }

    public function test_customer_cannot_access_admin_resources(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        foreach ([
            '/api/cash-flows',
            '/api/users',
        ] as $route) {
            $this->actingAs($customer, 'sanctum')->getJson($route)->assertForbidden();
        }

        $this->actingAs($customer, 'sanctum')->getJson('/api/services')->assertOk();
    }

    public function test_admin_can_create_customer_with_sanctum_token(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/customers', [
                'name' => 'Test Customer',
                'phone' => '08123456789',
                'address' => 'Jl. Contoh 1',
                'is_member' => true,
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Test Customer']);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'phone' => '08123456789',
        ]);
    }
}
