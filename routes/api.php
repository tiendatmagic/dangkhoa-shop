<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'order'
], function ($router) {
    Route::post('confirm', [AuthController::class, 'confirmOrder']);
    Route::get('get-order', [AuthController::class, 'getOrder']);
    Route::get('get-my-order', [AuthController::class, 'getMyOrder']);
    Route::get('get-order-detail', [AuthController::class, 'getOrderDetail']);
});

Route::group(
    [
        'middleware' => 'api',
    ],
    function ($router) {
        Route::post('register', [RegisterController::class, 'register']);
    }
);
