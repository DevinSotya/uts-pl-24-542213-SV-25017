<?php

use App\Http\Controllers\HealthController;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // /health didaftarkan tanpa middleware web/api (tanpa session dan tanpa token),
            // supaya tetap bisa menjawab 503 saat database mati dan bisa dipakai healthcheck.
            Route::get('/health', HealthController::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API tidak punya halaman login: tamu tidak diarahkan ke route('login').
        $middleware->redirectGuestsTo(fn (Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Semua error di /api/* selalu dirender sebagai JSON (401, 422, 404, dst.),
        // walau klien (mis. Postman default) tidak mengirim Accept: application/json.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Kegagalan database dicatat tanpa pesan asli: pesan QueryException memuat SQL
        // beserta nilai binding (bisa berisi email), jadi hanya kode dan sebab ringkas yang dicatat.
        $exceptions->report(function (QueryException $e) {
            Log::error('Database query failed', [
                'event' => 'db.query_failed',
                'actor' => 'http_request',
                'action' => request()->method().' /'.request()->path(),
                'reason' => match ($e->errorInfo[1] ?? null) {
                    2002 => 'database unreachable',
                    1045 => 'access denied',
                    2006 => 'server has gone away',
                    default => 'query failed',
                },
                'sqlstate' => $e->errorInfo[0] ?? null,
            ]);
        })->stop();
    })->create();