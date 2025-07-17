<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductLookupController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Artisan;

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

Route::post('/user/login', [AuthController::class, 'loginMobile']);

// Protected route example:
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', function (Request $request) {
        return $request->user();
    });

    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::post('/customer/register', [CustomerController::class, 'register']);
    Route::post('/products/fetch', [ProductLookupController::class, 'fetchFromPipo']);
    Route::get('/customer/active-customers', [CustomerController::class, 'getActiveCustomers']);
    Route::get('/order-report', [ReportController::class, 'orderReport']);
    Route::get('/order-report/{orderItemId}/serials', [ReportController::class, 'serialReport']);




    Route::prefix('cart')->group(function () {
        Route::post('/add-cart', [CartController::class, 'store']);
        Route::post('/{cartId}/add-item', [CartController::class, 'addItem']);
        Route::get('/{cart}', [CartController::class, 'getCartDetails']);
        Route::post('/{cartId}/remove-item', [CartController::class, 'removeItem']);
        Route::delete('/{cart}', [CartController::class, 'destroy']);
        Route::post('/details', [CartController::class, 'details']);
        Route::post('/{cart}/checkout', [CartController::class, 'checkout']);
    });
});

Route::post('/pos/mobile-app-order-request', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'getCartDetails']);
Route::post('/pos/add-cart-item', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'addItem']);
Route::post('/pos/remove-cart-item', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'removeItem']);
Route::post('/pos/check-serial-number', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'checkSerialNumber']);
Route::post('/pos/fetch', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'fetchFromPipo']);
Route::post('/pos/customer/register', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'customerRegistration']);
Route::post('/pos/checkout', [\App\Http\Controllers\Api\Pos\PosCustomerCartController::class, 'checkout']);
