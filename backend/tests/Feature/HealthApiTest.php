<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthApiTest extends TestCase
{
    public function test_health_returns_200_when_database_and_cache_are_ok(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('failed', [])
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_returns_503_and_names_the_failing_component_when_database_is_down(): void
    {
        DB::shouldReceive('select')->andThrow(new \RuntimeException('simulated database outage'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('failed', ['database'])
            ->assertJsonPath('checks.database.status', 'fail')
            ->assertJsonPath('checks.cache.status', 'ok');
    }
}