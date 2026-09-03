<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoogleAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.google.client_id', 'web-client.apps.googleusercontent.com');
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'admin']);
    }

    public function test_google_login_uses_verified_identity_instead_of_client_email(): void
    {
        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'web-client.apps.googleusercontent.com',
                'sub' => 'google-user-123',
                'email' => 'verified@example.com',
                'email_verified' => 'true',
                'name' => 'Verified User',
            ]),
        ]);

        $this->postJson('/api/auth/google', [
            'id_token' => 'valid-token',
            'email' => 'admin@example.com',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['access_token']]);

        $this->assertDatabaseHas('users', [
            'email' => 'verified@example.com',
            'google_id' => 'google-user-123',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_google_login_rejects_wrong_audience(): void
    {
        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'attacker-client.apps.googleusercontent.com',
                'sub' => 'google-user-123',
                'email' => 'verified@example.com',
                'email_verified' => true,
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'wrong-audience'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_token');
    }

    public function test_google_login_cannot_issue_a_token_for_staff_account(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');
        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'web-client.apps.googleusercontent.com',
                'sub' => 'google-admin-123',
                'email' => 'admin@example.com',
                'email_verified' => true,
            ]),
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'staff-token'])
            ->assertForbidden();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
