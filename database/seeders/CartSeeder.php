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
 
         // 確保至少有學生和課程，避免空值錯誤
         if (empty($students) || empty($courses)) {
             return;
         }
 
         foreach ($students as $studentId) {
             // 每位學生隨機將 1~3 門課程加入購物車
             $selectedCourses = array_rand($courses, rand(1, 3));
 
             foreach ((array) $selectedCourses as $index) {
                 Cart::create([
                     'user_id' => $studentId,
                     'course_id' => $courses[$index],
                     'quantity' => 1,
                     'price' => rand(1000, 5000),
                 ]);
             }
    }
}
}