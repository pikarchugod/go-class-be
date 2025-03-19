<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 先停用外鍵約束
    DB::table('courses')->truncate(); // 清空資料表
    DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 重新啟用外鍵約束

    $categories = Category::pluck('id')->toArray();   
    $teachers = User::where('role', 'teacher')->pluck('id')->toArray(); // 取得老師 ID

    $courses = [
        ['title' => 'Laravel 初學指南', 'description' => '適合新手學習 Laravel', 'price' => 1999],
        ['title' => '進階 Vue.js 開發', 'description' => '學習 Vue.js 深入應用', 'price' => 2999],
        ['title' => 'Python 資料科學入門', 'description' => '掌握 Pandas 與 Numpy', 'price' => 2499],
        ['title' => '前端基礎 CSS + JS', 'description' => '學習網頁前端開發基礎', 'price' => 1599],
        ['title' => '進階 AI 機器學習', 'description' => '學習機器學習算法', 'price' => 1899],
    ];
    
    foreach ($courses as $course) {
        Course::create([
            'title'       => $course['title'],
            'description' => $course['description'],
            'price'       => $course['price'],
            'teacher_id'  => count($teachers) > 0 ? $teachers[array_rand($teachers)] : null, // 避免找不到 teacher
            'category_id' => count($categories) > 0 ? $categories[array_rand($categories)] : null,
        ]);
    }
}
}