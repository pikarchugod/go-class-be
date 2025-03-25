<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\User;
use App\Models\Course;


class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 取得所有學生的 ID
        $students = User::where('role', 'student')->pluck('id')->toArray();
        // 取得所有課程的 ID
        $courses = Course::pluck('id')->toArray();

        // 如果沒有學生或課程則跳過
        if (empty($students) || empty($courses)) {
            return;
        }

        foreach ($students as $studentId) {
            // 每位學生隨機將 1~3 門課程加入購物車
            $numCourses = rand(1, 3);
            $selectedCourseIds = array_rand(array_flip($courses), $numCourses);

            // 保證 $selectedCourseIds 為陣列
            $selectedCourseIds = is_array($selectedCourseIds) ? $selectedCourseIds : [$selectedCourseIds];

            foreach ($selectedCourseIds as $courseId) {
                // 取得課程價格
                $course = Course::find($courseId);
                if (!$course) continue;

                Cart::create([
                    'user_id'   => $studentId,
                    'course_id' => $courseId,
                    'quantity'  => 1,
                    'price'     => $course->price,
                ]);
            }
        }
    }
}
