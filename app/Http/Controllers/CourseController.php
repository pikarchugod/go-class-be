<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * 取得符合搜尋、分類、價格篩選與排序條件的所有課程。
     */
    public function index(Request $request)
    {
        // 1. 先選取課程表所有欄位 + 老師名稱 + 平均評分
        //    - 透過 LEFT JOIN users 取得 teacher_name
        //    - 透過 LEFT JOIN reviews 計算平均評分
        // 2. groupBy 所有欄位，避免在 MySQL 嚴格模式下出錯

        $query = Course::select(
            'courses.id',
            'courses.title',
            'courses.description',
            'courses.price',
            'courses.teacher_id',
            'courses.category_id',
            'courses.cover_image',
            'courses.status',
            'courses.created_at',
            'courses.updated_at',

            // 從 users.name 取出老師名稱
            DB::raw('users.name as teacher_name'),

            // 計算平均評分，若無評分則為 0，並取到小數點一位
            DB::raw('COALESCE(ROUND(AVG(reviews.rating), 1), 0) as rating')
        )
            ->leftJoin('users', 'users.id', '=', 'courses.teacher_id')
            ->leftJoin('reviews', 'reviews.course_id', '=', 'courses.id')
            ->groupBy(
                'courses.id',
                'courses.title',
                'courses.description',
                'courses.price',
                'courses.teacher_id',
                'courses.category_id',
                'courses.cover_image',
                'courses.status',
                'courses.created_at',
                'courses.updated_at',
                'users.name'
            );

        // 1️⃣ 關鍵字搜尋（title / description）
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 2️⃣ 其他篩選條件（例如 category_id, min_price, max_price）若需要可放在這裡
        //    此處省略，僅示範

        // 3️⃣ 排序
        if ($request->has('sort')) {
            switch ($request->input('sort')) {
                case 'rating':
                    // 因為我們已經計算出 rating (AVG)，可以直接 orderBy('rating', 'desc')
                    $query->orderBy('rating', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'time_asc':
                    $query->orderBy('courses.created_at', 'asc');
                    break;
                case 'time_desc':
                    $query->orderBy('courses.created_at', 'desc');
                    break;
                case 'relevant':
                default:
                    // 不做特殊排序
                    break;
            }
        }

        // 4. 取得結果
        $courses = $query->get();
        return response()->json($courses);
    }

    /**
     * 取得當前登入使用者已購買的課程。
     */
    public function myCourses(Request $request)
    {
        $user = auth()->user();

        $courses = Course::whereHas('orderItems.order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', 'paid');
        })->get();

        return response()->json(['courses' => $courses]);
    }

    public function show($id)
    {
        $course = Course::select(
            'courses.id',
            'courses.title',
            'courses.description',
            'courses.price',
            'courses.teacher_id',
            'courses.category_id',
            'courses.cover_image',
            'courses.status',
            'courses.created_at',
            'courses.updated_at',
            DB::raw('users.name as teacher_name'),
            DB::raw('COALESCE(ROUND(AVG(reviews.rating), 1), 0) as rating')
        )
            ->leftJoin('users', 'users.id', '=', 'courses.teacher_id')
            ->leftJoin('reviews', 'reviews.course_id', '=', 'courses.id')
            ->where('courses.id', $id)
            ->groupBy(
                'courses.id',
                'courses.title',
                'courses.description',
                'courses.price',
                'courses.teacher_id',
                'courses.category_id',
                'courses.cover_image',
                'courses.status',
                'courses.created_at',
                'courses.updated_at',
                'users.name'
            )
            ->first();

        if (!$course) {
            return response()->json(['message' => '課程不存在'], 404);
        }

        // 取得章節
        // 若已在 Course Model 做 hasMany(Chapter::class)，可用:
        // $course->chapters = $course->chapters()->orderBy('sort_order')->get();
        // 或直接手動查詢：
        $course->chapters = \App\Models\Chapter::where('course_id', $course->id)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 假設問答目前沒有實作
        $course->qna = [];

        // 若尚未有購買人數，可預設 0
        $course->purchase_count = 0;

        return response()->json($course);
    }
}
