<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // 取得所有學生的 ID
        $students = User::where('role', 'student')->pluck('id')->toArray();
        // 取得所有課程的 ID
        $courses = Course::pluck('id')->toArray(); // 這裡修正為只有 ID

        if (empty($students) || empty($courses)) {
            $this->command->info('⚠️ 沒有學生或課程，跳過訂單 Seeder！');
            return;
        }

        foreach ($students as $student_id) {
            // 隨機選擇 1~3 門課程
            $selectedCourses = array_rand($courses, rand(1, 3));

            if (!is_array($selectedCourses)) {
                $selectedCourses = [$selectedCourses];
            }

            // 計算總金額
            $totalAmount = array_sum(array_map(fn($key) => Course::find($courses[$key])->price, $selectedCourses));

            // 建立訂單
            $order = Order::create([
                'user_id' => $student_id,
                'order_number' => 'ORD' . strtoupper(uniqid()), // 產生唯一訂單號
                'total_amount' => $totalAmount,
                'status' => 'paid', // 假設都已付款
                'payment_method' => 'credit_card', // 假設付款方式
                'trade_no' => 'TRADE' . strtoupper(uniqid()), // 模擬交易號
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 插入 order_items
            foreach ($selectedCourses as $courseKey) {
                $courseId = $courses[$courseKey];
                $price = Course::find($courseId)->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $courseId,
                    'price' => $price,
                    'quantity' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
         }
    }
}
