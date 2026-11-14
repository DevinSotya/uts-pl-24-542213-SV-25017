<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HealthController extends Controller
{
    /**
     * GET /health -> 200 bila database dan cache sehat, 503 bila salah satu gagal.
     * Body menyebut komponen mana yang bermasalah.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check('database', function () {
                DB::select('select 1');
            }),
            'cache' => $this->check('cache', function () {
                $key = 'health:probe:'.Str::random(8);

                Cache::put($key, 'ok', 10);
                $value = Cache::get($key);
                Cache::forget($key);

                if ($value !== 'ok') {
                    throw new RuntimeException('cache read-after-write mismatch');
                }
            }),
        ];

        $failed = array_keys(array_filter($checks, fn (array $check) => $check['status'] !== 'ok'));
        $healthy = $failed === [];

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'failed' => $failed,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    /**
     * Jalankan satu pemeriksaan. Penyebab detail hanya masuk ke log;
     * respons publik cuma menyebut komponen yang gagal.
     */
    private function check(string $component, callable $probe): array
    {
        $start = microtime(true);

        try {
            $probe();
        } catch (Throwable $e) {
            Log::error('Health check failed', [
                'event' => 'health.check_failed',
                'actor' => 'healthcheck',
                'action' => 'check_'.$component,
                'component' => $component,
                'reason' => $e->getMessage(),
            ]);

            return ['status' => 'fail', 'error' => $component.' check failed'];
        }

        return [
            'status' => 'ok',
            'latency_ms' => round((microtime(true) - $start) * 1000, 1),
        ];
    }
}