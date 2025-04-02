<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 停用外鍵約束，清空表，再啟用外鍵
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('courses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. 假設你在 storage/app/public/uploads/courses/ 下有 1.png ~ 5.png
        //    只要在 coverImages 中隨機選一張即可
        $coverImages = [
            '1.png',
            '2.png',
            '3.png',
            '4.png',
            '5.png',
        ];

        // 3. 要插入的課程資料
        $courses = [
            ['title' => '圍棋入門教學',  'description' => '適合入門學習',  'price' => 2000],
            ['title' => '圍棋初階教學',  'description' => '適合初階學習',  'price' => 3000],
            ['title' => '圍棋進階教學',  'description' => '適合進階學習',  'price' => 4000],
            ['title' => '圍棋中階教學',  'description' => '適合中階學習',  'price' => 5000],
            ['title' => '圍棋高階教學',  'description' => '適合高階學習',  'price' => 6000],
        ];

        // 4. 逐筆插入資料
        foreach ($courses as $course) {
            Course::create([
                'title'       => $course['title'],
                'description' => $course['description'],
                'price'       => $course['price'],

                // 既然你只有一位老師，且 ID=2，就固定填 2
                'teacher_id'  => 2,

                // 若只想固定一個分類 ID = 2，就這樣指定
                'category_id' => 2,

                // 從 coverImages 中隨機選一張
                'cover_image' => 'uploads/courses/' . $coverImages[array_rand($coverImages)],

                // 狀態固定 draft，若想要 published 就改
                'status'      => 'draft',
            ]);
        }
    }
}
