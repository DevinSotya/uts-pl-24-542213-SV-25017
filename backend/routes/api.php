<?php
 
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 
// Satu-satunya endpoint publik.
Route::post('/login', [AuthController::class, 'login']);
 
// Semua endpoint lain wajib token.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
 
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
 
    // Resource books ditambahkan di feature/crud-api.
});