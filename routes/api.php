<?php

use App\Http\Controllers\Api\V1\App\User\UserBackupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\App\Auth\AuthController;
use App\Http\Controllers\Api\V1\App\User\UserProfileController;

// use App\Http\Controllers\Api\V1\Dashboard\User\UserController;
// use App\Http\Controllers\Api\V1\Select\SelectController;
// use App\Http\Controllers\Api\V1\Dashboard\User\UserProfileController;
// use App\Http\Controllers\Api\V1\Dashboard\User\ChangePasswordController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login','login');
        Route::post('/register','register');
        Route::post('/logout','logout');
    });

    // // Users
    // Route::apiResource('users', UserController::class);
    Route::apiSingleton('user-profile', UserProfileController::class);
    // Route::put('user-profile/change-password', ChangePasswordController::class);

    // // Select
    // Route::prefix('selects')->group(function(){
    //     Route::get('', [SelectController::class, 'getSelects']);
    // });

    // // User Backups
    Route::controller(UserBackupController::class)->prefix('user-backups')->group(function () {
        Route::put('','update');
        Route::get('', 'show');
    });
});
