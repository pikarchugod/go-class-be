<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 這裡放「前後端分離」要用的 API，皆走 Token 模式 (auth:sanctum)。
| 不需要 sanctum/csrf-cookie，也不會產生 419。
|
*/

// 測試用
Route::get('/test', function () {
    return response()->json(['msg' => 'ok']);
});

// 取得使用者資料 (需要 token)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// === 基本「註冊 / 登入」(不需要 token) ===
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// === 需要登入才可使用的路由 ===
Route::middleware('auth:sanctum')->group(function () {

    // 購物車
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);

    // 結帳 & 訂單
    Route::post('/order/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::delete('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // 收藏
    Route::post('/favorites/{course}', [FavoriteController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::delete('/favorites/{course}', [FavoriteController::class, 'destroy']);

    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');

    // 登出
    Route::post('/logout', [AuthController::class, 'logout']);

    // 這裡也可以放其他需要驗證的 API
    Route::get('/user-info', function (Request $request) {
        return $request->user();
    });
});

// 忘記密碼
Route::post('/password/email', [AuthController::class, 'sendResetLink']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// 搜尋結果
Route::get('/courses', [CourseController::class, 'index']);

// 付款
Route::post('/order/pay', [OrderController::class, 'pay']);
Route::get('/order/payment-success', [OrderController::class, 'paymentSuccess']);
