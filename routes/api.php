<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('/token', [AuthController::class, 'generateToken']);
});

Route::middleware(['api.auth'])->group(function () {
    Route::post('/auth/revoke', [AuthController::class, 'revokeTokens']);
});
