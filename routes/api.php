<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers\API')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/token', 'AuthController@generateToken');
    });

    Route::namespace('Report')->group(function () {
        Route::apiResources([
            'rpt-trans-yearly' => 'YearlyTransactionController',
            'rpt-target-revenue' => 'TargetRevenueController',
        ]);
    });
    
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/auth/revoke', 'AuthController@revokeTokens');
    });
});