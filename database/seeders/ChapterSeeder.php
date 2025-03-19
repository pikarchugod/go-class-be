<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Chapter;
use App\Models\Course;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 取得所有課程的 ID
        $courses = Course::pluck('id')->toArray();

        // 測試章節內容
        $chapters = [
            ['title' => '基礎介紹', 'video_url' => 'https://test.video.url/intro'],
            ['title' => '進階應用', 'video_url' => 'https://test.video.url/advanced'],
            ['title' => '專案實作', 'video_url' => 'https://test.video.url/project']
        ];

        foreach ($courses as $courseId) {
            // 每個課程隨機建立 2-3 個章節
            $randomChapters = array_rand($chapters, rand(2, 3));

            foreach ((array) $randomChapters as $index) {
                Chapter::create([
                    'course_id' => $courseId,
                    'title' => $chapters[$index]['title'],
                    'video_url' => $chapters[$index]['video_url'],
                    'sort_order' => rand(1, 10),
                ]);
            }
        }
    }
}
