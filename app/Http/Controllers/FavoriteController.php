<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;

class FavoriteController extends Controller
{
     // 1️⃣ 收藏課程
     public function store($courseId)
     {
         $user = auth()->user();
         $user->favorites()->attach($courseId);
 
         return response()->json(['message' => '課程已加入收藏']);
     }
 
     // 2️⃣ 查看收藏清單
     public function index()
     {
         $user = auth()->user();
         $favorites = $user->favorites()->with('category')->get();
 
         return response()->json(['favorites' => $favorites]);
     }
 
     // 3️⃣ 取消收藏
     public function destroy($courseId)
     {
         $user = auth()->user();
         $user->favorites()->detach($courseId);
 
         return response()->json(['message' => '課程已取消收藏']);
     }
}
