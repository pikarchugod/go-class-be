<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Favorite;

class UserController extends Controller
{
    /**
     * 取得使用者已購買 (paid) 的所有課程
     * GET /my-courses
     *
     * 回傳格式：
     * {
     *   "courses": [
     *     {
     *       "id": ...,
     *       "title": "...",
     *       "cover_image": "...", // 原始相對路徑
     *       "cover_url": "http://your-app-url/storage/...", // 完整圖片 URL
     *       "price": ...,
     *       ...
     *     },
     *     ...
     *   ]
     * }
     */
    public function myCourses(Request $request)
    {
        $user = $request->user();

        // 透過 orderItems.order 找出該使用者已付款的課程
        $courses = Course::whereHas('orderItems.order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', 'paid');
        })->get();

        // 取得基礎 URL，請確認 .env 中有設定 APP_URL
        $baseUrl = config('app.url');

        // 處理圖片：組合 cover_image 成完整 URL
        $courses = $courses->map(function ($course) use ($baseUrl) {
            $course->cover_url = $course->cover_image
                ? $baseUrl . '/storage/' . $course->cover_image
                : '/default-course.png';
            return $course;
        });

        return response()->json([
            'courses' => $courses
        ]);
    }
}
