<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBearerTokenTest extends TestCase
{
    use RefreshDatabase;

    private string $bearerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bearerToken = env('API_BEARER_TOKEN', 'testing-token');
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

    public function test_api_endpoints_are_accessible_with_valid_bearer_token(): void
    {
        Customer::create([
            'name' => 'Existing Customer',
            'phone' => '08123456789',
            'address' => 'Jl. Contoh',
            'is_member' => false,
        ]);

        $routes = [
            '/api/cash-flows',
            '/api/customers',
            '/api/orders',
            '/api/services',
            '/api/users',
        ];

        foreach ($routes as $route) {
            $this->getJson($route, ['Authorization' => 'Bearer '.$this->bearerToken])
                ->assertOk();
        }
    }

    public function test_can_create_customer_through_api_with_valid_bearer_token(): void
    {
        $this->postJson(
            '/api/customers',
            [
                'name' => 'Test Customer',
                'phone' => '08123456789',
                'address' => 'Jl. Contoh 1',
                'is_member' => true,
            ],
            ['Authorization' => 'Bearer '.$this->bearerToken]
        )
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Test Customer']);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'phone' => '08123456789',
        ]);
    }
}
