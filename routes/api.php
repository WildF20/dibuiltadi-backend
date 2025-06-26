<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers\API')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/token', 'AuthController@generateToken');
    });
    
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/auth/revoke', 'AuthController@revokeTokens');
    });
});