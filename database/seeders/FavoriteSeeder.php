<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Course;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 取得所有學生
        $students = User::where('role', 'student')->pluck('id')->toArray();
        // 取得所有課程
        $courses = Course::pluck('id')->toArray();

        // 如果學生或課程數量為 0，則不執行
        if (empty($students) || empty($courses)) {
            return;
        }

        // 每個學生隨機收藏 1~3 門課
        foreach ($students as $student) {
            $favoriteCourses = array_rand(array_flip($courses), rand(1, 3));

            // 確保轉為陣列格式
            if (!is_array($favoriteCourses)) {
                $favoriteCourses = [$favoriteCourses];
            }

            foreach ($favoriteCourses as $course) {
                Favorite::create([
                    'user_id'   => $student,
                    'course_id' => $course,
                ]);
            }
        }
    }
}
