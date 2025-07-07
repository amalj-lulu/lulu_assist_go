<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductLookupController;

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



    Route::prefix('cart')->group(function () {
        Route::post('/', [CartController::class, 'store']);
        Route::post('/{cartId}/add-item', [CartController::class, 'addItem']);
        Route::get('/{cart}', [CartController::class, 'getCartDetails']);
        Route::post('/{cartId}/remove-item', [CartController::class, 'removeItem']);
        Route::delete('/{cart}', [CartController::class, 'destroy']);
        Route::post('/details', [CartController::class, 'details']);
    }); 
});
