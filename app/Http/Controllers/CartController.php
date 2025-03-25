<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;
use App\Models\Course;

class CartController extends Controller
{
    /**
     * 加入購物車
     * POST /api/cart/add
     * Body: { "course_id": <課程ID> }
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $user = auth()->user();

        // 檢查該課程是否已存在購物車中
        if ($user->carts()->where('course_id', $request->course_id)->exists()) {
            // 若你想更新數量，可改為：
            // $cartItem = $user->carts()->where('course_id', $request->course_id)->first();
            // $cartItem->increment('quantity');
            // return response()->json(['message' => '購物車數量已更新', 'cart' => $cartItem], 200);
            return response()->json(['message' => '課程已在購物車中'], 400);
        }

        // 取得課程價格，建議用 findOrFail
        $course = Course::findOrFail($request->course_id);

        // 新增購物車項目
        $cartItem = Cart::create([
            'user_id'   => $user->id,
            'course_id' => $course->id,
            'quantity'  => 1,  // 預設數量 1
            'price'     => $course->price,
        ]);

        return response()->json([
            'message' => '課程已加入購物車',
            'cart'    => $cartItem
        ], 201);
    }

    /**
     * 查看購物車
     * GET /api/cart
     */
    public function viewCart()
    {
        $user = auth()->user();
        // 載入每個 cart 項目關聯的課程資料
        $cartItems = $user->carts()->with('course')->get();

        return response()->json([
            'cart' => $cartItems
        ]);
    }

    /**
     * 移除購物車項目
     * DELETE /api/cart/remove/{id}
     * 此處 id 為購物車項目的 id
     */
    public function removeFromCart($id)
    {
        $user = auth()->user();
        $cartItem = $user->carts()->find($id);

        if (!$cartItem) {
            return response()->json(['message' => '購物車內無此項目'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => '課程已從購物車移除']);
    }
}
