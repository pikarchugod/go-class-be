<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();

        // 取得購物車內容 (含課程資料)
        $cartItems = $user->carts()->with('course')->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => '購物車是空的'], 400);
        }

        // 計算總金額（確保為整數）
        $totalAmount = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        // 產生唯一的 merchant_trade_no
        $merchantTradeNo = 'ORDER' . Str::random(10);

        // 建立訂單，寫入 orders 資料表
        $order = Order::create([
            'user_id'           => $user->id,
            'order_number'      => uniqid('ORDER_'),
            'merchant_trade_no' => $merchantTradeNo,
            'total_amount'      => $totalAmount,
            'status'            => 'pending',  // 初始狀態為 pending
            'payment_method'    => 'credit_card',
        ]);

        // 建立訂單明細
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id'  => $order->id,
                'course_id' => $cartItem->course_id,
                'price'     => $cartItem->price,
                'quantity'  => $cartItem->quantity,
            ]);
        }

        // 清空購物車
        $user->carts()->delete();

        return response()->json([
            'message' => '訂單已建立，等待付款',
            'order'   => $order
        ], 201);
    }

    /**
     * 付款：發起付款請求（模擬 ECPay 流程）
     * POST /api/order/pay
     * Body: { "order_id": <orderId> }
     */
    public function pay(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        // 若訂單已付款
        if ($order->status === 'paid') {
            return response()->json(['message' => '此訂單已付款'], 400);
        }

        // 產生模擬付款連結（未來可整合 ECPay）
        $paymentUrl = url("/fake-payment/{$order->id}");

        return response()->json([
            'message' => '請前往付款',
            'payment_url' => $paymentUrl
        ]);
    }

    /**
     * 付款成功回調（模擬付款成功後更新狀態）
     * GET /api/order/payment-success
     * Query: order_id
     */
    public function paymentSuccess(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        // 若已付款則回應錯誤
        if ($order->status === 'paid') {
            return response()->json(['message' => '此訂單已付款'], 400);
        }

        // 更新訂單狀態為 paid
        $order->update(['status' => 'paid']);

        return response()->json(['message' => '付款成功！']);
    }

    /**
     * 查詢訂單清單 (GET /api/orders)
     */
    public function index()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('items.course')->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * 取得單筆訂單詳情 (GET /api/orders/{id})
     */
    public function show($id)
    {
        $user = auth()->user();
        $order = $user->orders()->with('items.course')->find($id);

        if (!$order) {
            return response()->json(['message' => '訂單不存在'], 404);
        }

        return response()->json(['order' => $order]);
    }

    /**
     * 取消訂單：僅限未付款 (pending) 的訂單 (DELETE /api/orders/{id}/cancel)
     */
    public function cancel($id)
    {
        $user = auth()->user();
        $order = $user->orders()->where('status', 'pending')->find($id);

        if (!$order) {
            return response()->json(['message' => '無法取消，訂單不存在或已付款'], 400);
        }

        $order->update(['status' => 'canceled']);

        return response()->json([
            'message' => '訂單已取消',
            'order'   => $order
        ]);
    }
}
