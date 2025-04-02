<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class FavoriteController extends Controller
{
    /**
     * 收藏課程
     * POST /favorites/{courseId}
     *
     * 若該課程已收藏，則回傳錯誤訊息。
     */
    public function store($courseId)
    {
        $user = auth()->user();

        // 避免重複收藏
        if ($user->favorites()->where('course_id', $courseId)->exists()) {
            return response()->json(['message' => '已收藏該課程'], 400);
        }

        // 加入收藏
        $user->favorites()->attach($courseId);

        return response()->json(['message' => '課程已加入收藏']);
    }

    /**
     * 查看收藏清單
     * GET /favorites
     *
     * 回傳結構範例：
     * {
     *    "favorites": [
     *       {
     *          "id": 1,
     *          "title": "課程標題",
     *          "cover_url": "http://your-app-url/storage/uploads/courses/xxx.png",
     *          "price": 3000,
     *          "category": { ... }
     *       },
     *       ...
     *    ]
     * }
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 取得該使用者所有收藏的課程，並載入關聯的 category（若需要）
        $favorites = $user->favorites()->with('category')->get();

        // 取得基礎 URL，請確認 .env 中有設定 APP_URL
        $baseUrl = config('app.url');

        // 處理每筆收藏資料：組合完整圖片 URL (cover_url)
        $favorites = $favorites->map(function ($course) use ($baseUrl) {
            $course->cover_url = $course->cover_image
                ? $baseUrl . '/storage/' . $course->cover_image
                : '/default-course.png';
            return $course;
        });

        return response()->json([
            'favorites' => $favorites->values() // 轉為純陣列
        ]);
    }

    /**
     * 取消收藏
     * DELETE /favorites/{courseId}
     */
    public function destroy($courseId)
    {
        $user = auth()->user();
        $user->favorites()->detach($courseId);

        return response()->json(['message' => '課程已取消收藏']);
    }
}
