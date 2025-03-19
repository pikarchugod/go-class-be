<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 停用外鍵檢查
        DB::table('users')->truncate();
        DB::table('categories')->truncate();
        DB::table('courses')->truncate();
        DB::table('chapters')->truncate();
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('carts')->truncate();
        DB::table('favorites')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // 恢復外鍵檢查
    
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            ChapterSeeder::class,
            OrderSeeder::class,
            CartSeeder::class,
            FavoriteSeeder::class,
        ]);

    }
}
