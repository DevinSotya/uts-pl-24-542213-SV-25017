<?php
 
namespace Tests\Feature;
 
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
 
class AuthApiTest extends TestCase
{
    use RefreshDatabase;
 
    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-123']);
 
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-pass-123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type'])
            ->assertJson(['token_type' => 'Bearer']);
    }
 
    public function test_login_with_wrong_password_returns_401(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-123']);
 
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }
 
    public function test_login_without_fields_returns_422(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
 
    public function test_request_without_token_returns_401_json(): void
    {
        // Sengaja tanpa header Accept: application/json.
        $this->get('/api/user')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
 
    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
 
        $this->withToken($token)->postJson('/api/logout')->assertOk();
 
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}