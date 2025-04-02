<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        $courseIds = Course::pluck('id')->toArray();

        // 測試章節資料，依序排列
        $chaptersData = [
            ['title' => '基礎概念', 'video_url' => 'https://www.youtube.com/watch?v=yYUQf6G4CJc'],
            ['title' => '進階應用', 'video_url' => 'https://www.youtube.com/watch?v=V7Bohz21qq4'],
            ['title' => '專案實作', 'video_url' => 'https://www.youtube.com/watch?v=vNfs7VU_hrQ'],
        ];

        // 對於每個課程，依序建立 3 個章節 (sort_order 依序 1,2,3)
        foreach ($courseIds as $courseId) {
            foreach ($chaptersData as $index => $data) {
                Chapter::create([
                    'course_id'  => $courseId,
                    'title'      => $data['title'],
                    'video_url'  => $data['video_url'],
                    'sort_order' => $index + 1, // 1, 2, 3
                ]);
            }
        }
    }
}
