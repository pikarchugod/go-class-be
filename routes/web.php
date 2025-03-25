<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
// 後端回調 (POST)
Route::post('/ecpay/callback', [PaymentController::class, 'paymentCallback'])->name('ecpay.callback');

// 前端跳轉 (GET)
Route::get('/ecpay/redirect', [PaymentController::class, 'paymentRedirect'])->name('ecpay.redirect');


Route::get('/', function () {
    return view('welcome');
});
