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
    Route::post('login-2fa', [AuthController::class, 'login2fa']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::post('2fa/status', [AuthController::class, 'twoFactorStatus']);
    Route::post('2fa/generate', [AuthController::class, 'twoFactorGenerate']);
    Route::post('2fa/enable', [AuthController::class, 'twoFactorEnable']);
    Route::post('2fa/disable', [AuthController::class, 'twoFactorDisable']);
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
    Route::post('cancel', [AuthController::class, 'cancelOrder']);
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
// Sepay webhook (no auth)
Route::post('sepay/webhook', [AuthController::class, 'sepayWebhook']);

Route::group([
    'middleware' => ['api', 'admin'],
], function ($router) {
    Route::get('overview', [AdminController::class, 'getOverview']);
    Route::get('wallet-settings', [AdminController::class, 'getWalletSettings']);
    Route::post('wallet-settings', [AdminController::class, 'updateWalletSettings']);

    Route::get('token-assets', [AdminController::class, 'getTokenAssets']);
    Route::post('token-assets', [AdminController::class, 'upsertTokenAsset']);
    Route::post('token-assets/delete', [AdminController::class, 'deleteTokenAsset']);
    // Admin customization endpoints
    Route::get('admin/customization', [AdminController::class, 'getCustomization']);
    Route::post('admin/customization', [AdminController::class, 'saveCustomization']);
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
    Route::post('delete-product-image', [AdminController::class, 'deleteProductImage']);
});

Route::group(
    [
        'middleware' => 'api',
    ],
    function ($router) {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('forgot-password', [RegisterController::class, 'forgotPassword']);
        Route::post('reset-password', [RegisterController::class, 'resetPassword']);
        Route::get('home', [HomeController::class, 'getHomeProducts']);
        Route::get('customization', [AdminController::class, 'getCustomization']);
        Route::get('collections', [HomeController::class, 'getCollections']);
        Route::get('products', [HomeController::class, 'getProducts']);
        Route::get('products/{id}', [HomeController::class, 'getProductById']);
    }
);
