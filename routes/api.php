<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\API'], function () {
    Route::prefix('auth')->group(function () {
        Route::post('/token', 'AuthController@generateToken');
    });
    
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/auth/revoke', 'AuthController@revokeTokens');
    });
});