<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\HomeController;
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
    Route::post('check-coinbase', [AuthController::class, 'checkCoinbaseStatus']);
});

Route::group([
    'middleware' => ['api', 'admin'],
    'prefix' => 'order'
], function ($router) {
    Route::get('get-all-order', [AdminController::class, 'getAllOrder']);
    Route::post('update-order-status', [AdminController::class, 'updateOrderStatus']);
    Route::get('get-order-detail-admin', [AdminController::class, 'getOrderDetailAdmin']);
});

// Coinbase webhook (no auth)
Route::post('coinbase/webhook', [AuthController::class, 'coinbaseWebhook']);

Route::group([
    'middleware' => ['api', 'admin'],
], function ($router) {
    Route::get('overview', [AdminController::class, 'getOverview']);
    Route::get('wallet-settings', [AdminController::class, 'getWalletSettings']);
    Route::post('wallet-settings', [AdminController::class, 'updateWalletSettings']);
});

Route::group([
    'middleware' => ['api', 'admin'],
    'prefix' => 'product'
], function ($router) {
    Route::get('get-all-product', [AdminController::class, 'getAllProduct']);
    Route::get('get-product-detail', [AdminController::class, 'getProductDetail']);
    Route::post('update-product', [AdminController::class, 'updateProduct']);
    Route::post('upload-image', [AdminController::class, 'upload']);
    Route::post('create-product', [AdminController::class, 'createProduct']);
    Route::post('delete-product', [AdminController::class, 'deleteProduct']);
});

Route::group(
    [
        'middleware' => 'api',
    ],
    function ($router) {
        Route::post('register', [RegisterController::class, 'register']);
        Route::get('home', [HomeController::class, 'getHomeProducts']);
        Route::get('products', [HomeController::class, 'getProducts']);
        Route::get('products/{id}', [HomeController::class, 'getProductById']);
    }
);
