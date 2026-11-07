<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StructuredLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_logs_warning_without_credentials(): void
    {
        Log::spy();
        $user = User::factory()->create(['password' => 'secret-pass-123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(401);

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) use ($user) {
            $json = json_encode($context);

            return $message === 'Login failed'
                && ($context['reason'] ?? null) === 'wrong_password'
                && ! str_contains($json, 'wrong-password')
                && ! str_contains($json, $user->email);
        })->once();
    }

    public function test_creating_a_book_logs_info_with_actor_and_book_id(): void
    {
        Log::spy();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/books', [
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'year' => 2008,
            'status' => 'reading',
        ])->assertCreated();

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context = []) use ($user, $response) {
            return $message === 'Book created'
                && ($context['actor'] ?? null) === 'user:'.$user->id
                && ($context['book_id'] ?? null) === $response->json('id');
        })->once();
    }

    public function test_invalid_book_logs_validation_warning_without_input_values(): void
    {
        Log::spy();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', [
            'title' => 'Secret Title',
            'status' => 'invalid',
        ])->assertStatus(422);

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) {
            return $message === 'Validation failed'
                && isset($context['failed_rules']['author'])
                && ! str_contains(json_encode($context), 'Secret Title');
        })->once();
    }
}