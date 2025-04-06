<?php

use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::group([
    "middleware" => ['auth:api']
], function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('profile', 'profile');
    });
});
