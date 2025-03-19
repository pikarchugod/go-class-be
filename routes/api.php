<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// 基本註冊 / 登入
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 需要登入才可使用的路由
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']); // 加入購物車
    Route::get('/cart', [CartController::class, 'viewCart']); // 查看購物車
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']); // 移除購物車項目
    Route::post('/order/checkout', [OrderController::class, 'checkout']); // 結帳 API
    Route::get('/my-courses', [CourseController::class, 'myCourses']);
    Route::get('/orders', [OrderController::class, 'index']);  // 查看所有訂單
    Route::get('/orders/{id}', [OrderController::class, 'show']);  // 查看單筆訂單
    Route::delete('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/favorites/{course}', [FavoriteController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::delete('/favorites/{course}', [FavoriteController::class, 'destroy']);
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // 這裡也可以放其他需要驗證的 API
    Route::get('/user-info', function (Request $request) {
        return $request->user();
    });
});

// 忘記密碼
Route::post('/password/email', [AuthController::class, 'sendResetLink']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

//搜尋結果
Route::get('/courses', [CourseController::class, 'index']);


Route::post('/order/pay', [OrderController::class, 'pay']);
Route::get('/order/payment-success', [OrderController::class, 'paymentSuccess']);