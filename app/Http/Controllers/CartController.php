<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;
use App\Models\Course;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);
    
        $user = auth()->user();
    
        // 檢查是否已經在購物車中
        if ($user->cart()->where('course_id', $request->course_id)->exists()) {
            return response()->json(['message' => '課程已在購物車中'], 400);
        }
    
        // 加入購物車
        $cartItem = Cart::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'quantity' => 1,  // 預設數量為 1
            'price' => Course::find($request->course_id)->price, // 取得課程價格
        ]);
    
        return response()->json(['message' => '課程已加入購物車', 'cart' => $cartItem], 201);
    }
    
  
      // 2️⃣ 查看購物車
      public function viewCart()
      {
          $user = auth()->user();
          $cartItems = $user->cart()->with('course')->get();
  
          return response()->json(['cart' => $cartItems]);
      }
  
      // 3️⃣ 移除購物車項目
      public function removeFromCart($id)
      {
          $user = auth()->user();
          $cartItem = $user->cart()->where('course_id', $id)->first();
  
          if (!$cartItem) {
              return response()->json(['message' => '購物車內無此課程'], 404);
          }
  
          $cartItem->delete();
  
          return response()->json(['message' => '課程已從購物車移除']);
      }
}
