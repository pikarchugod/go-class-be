<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reviews')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 假設你只想讓 role=student 的用戶評分
        $students = User::where('role', 'student')->pluck('id')->toArray();
        $courses = Course::pluck('id')->toArray();

        // 你可隨機產生 20 筆評價
        $numReviews = 20;

        for ($i = 0; $i < $numReviews; $i++) {
            Review::create([
                'user_id'   => $students[array_rand($students)],
                'course_id' => $courses[array_rand($courses)],
                'rating'    => rand(1, 5), // 1~5 的整數
                'comment'   => '這是一個測試評論 ' . ($i + 1),
            ]);
        }
    }
}
