<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 這裡放「前後端分離」要用的 API。所有需要驗證的 API 都放在同一個
| auth:sanctum 群組裡，只驗證一次 Token。
|
*/

// 公共路由：不需要 Token 驗證
Route::get('/test', function () {
    return response()->json(['msg' => 'ok']);
});

// 基本註冊/登入與密碼重置，不需要驗證
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLink']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// 以下所有路由都需要驗證
Route::middleware('auth:sanctum')->group(function () {
    // 使用者資料
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user-info', function (Request $request) {
        return $request->user();
    });

    // 課程
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);

    // 購物車
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);

    // 訂單
    Route::post('/order/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::delete('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // 收藏
    Route::post('/favorites/{course}', [FavoriteController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::delete('/favorites/{course}', [FavoriteController::class, 'destroy']);

    // 付款
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::post('/order/pay', [OrderController::class, 'pay']);
    Route::get('/order/payment-success', [OrderController::class, 'paymentSuccess']);

    // 我的課程
    Route::get('/my-courses', [UserController::class, 'myCourses']);

    // 登出
    Route::post('/logout', [AuthController::class, 'logout']);
});
