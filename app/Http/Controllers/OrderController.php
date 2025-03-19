<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;


class OrderController extends Controller
{
    public function checkout(Request $request)
{
    $user = auth()->user(); // 取得當前用戶

    // 取得購物車內容
    $cartItems = $user->cart()->with('course')->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => '購物車是空的'], 400);
    }

    // 計算總金額
    $totalAmount = $cartItems->sum(fn($item) => $item->price * $item->quantity);

    // 建立訂單
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => uniqid('ORDER_'), // 生成唯一訂單編號
        'total_amount' => $totalAmount,
        'status' => 'pending', // 預設未付款
        'payment_method' => 'credit_card', // 先預設信用卡
        'trade_no' => null, // 付款後才會有交易編號
    ]);

    // 建立 `order_items`
    foreach ($cartItems as $cartItem) {
        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $cartItem->course_id,
            'price' => $cartItem->price,
            'quantity' => $cartItem->quantity,
        ]);
    }

    // 清空購物車
    $user->cart()->delete();

    return response()->json([
        'message' => '訂單已建立，等待付款',
        'order' => $order,
    ], 201);

    
}

public function pay(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id'
    ]);

    $order = Order::find($request->order_id);

    // 檢查是否已付款
    if ($order->status === 'paid') {
        return response()->json(['message' => '此訂單已付款'], 400);
    }

    // 模擬付款連結（未來換成 ECPay）
    $paymentUrl = url("/fake-payment/{$order->id}");

    return response()->json([
        'message' => '請前往付款',
        'payment_url' => $paymentUrl
    ]);
}

public function paymentSuccess(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id'
    ]);

    $order = Order::find($request->order_id);
    
    // 確保訂單尚未付款
    if ($order->status === 'paid') {
        return response()->json(['message' => '此訂單已經付款過了'], 400);
    }

    // 更新訂單狀態
    $order->update(['status' => 'paid']);

    return response()->json(['message' => '付款成功！']);
}

public function index()
{
    $user = auth()->user();
    $orders = $user->orders()->with('items.course')->get();

    return response()->json(['orders' => $orders]);
}

public function show($id)
{
    $user = auth()->user();
    $order = $user->orders()->with('items.course')->find($id);

    if (!$order) {
        return response()->json(['message' => '訂單不存在'], 404);
    }

    return response()->json(['order' => $order]);
}

public function cancel($id)
{
    $user = auth()->user();
    $order = $user->orders()->where('status', 'pending')->find($id);

    if (!$order) {
        return response()->json(['message' => '無法取消，訂單不存在或已付款'], 400);
    }

    $order->update(['status' => 'canceled']);

    return response()->json(['message' => '訂單已取消', 'order' => $order]);
}

}
