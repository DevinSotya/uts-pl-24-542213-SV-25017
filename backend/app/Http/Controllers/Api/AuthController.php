<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login: 200 + token, 422 bila field kosong/tidak valid, 401 bila kredensial salah.
     * Log tidak pernah memuat email, password, atau token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('Login failed', [
                'event' => 'auth.login_failed',
                'actor' => 'guest',
                'action' => 'login',
                'reason' => $user ? 'wrong_password' : 'unknown_account',
            ]);

            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        Log::info('Login succeeded', [
            'event' => 'auth.login_succeeded',
            'actor' => 'user:'.$user->id,
            'action' => 'login',
            'reason' => 'valid_credentials',
        ]);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout: mencabut token yang sedang dipakai.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        Log::info('Logout', [
            'event' => 'auth.logout',
            'actor' => 'user:'.$request->user()->id,
            'action' => 'logout',
            'reason' => 'user_request',
        ]);

        return response()->json(['message' => 'Logged out.']);
    }
}