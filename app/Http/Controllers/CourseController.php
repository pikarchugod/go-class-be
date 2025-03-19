<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Course;


class CourseController extends Controller
{
    public function index(Request $request)
{
    $query = Course::query();

    // 1️⃣ 關鍵字搜尋
    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
    }

    // 2️⃣ 分類篩選
    if ($request->has('category_id')) {
        $query->where('category_id', $request->input('category_id'));
    }

    // 3️⃣ 價格篩選
    if ($request->has('min_price') && $request->has('max_price')) {
        $query->whereBetween('price', [$request->input('min_price'), $request->input('max_price')]);
    }

    return response()->json($query->get());
}
    
    public function myCourses()
    {
        $user = auth()->user();

        // 透過 orders 取得已購買課程
        $courses = Course::whereHas('orderItems.order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'paid');
        })->get();

        return response()->json(['courses' => $courses]);
    }

}
