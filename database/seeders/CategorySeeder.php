<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['程式設計', '商業行銷', '設計', '攝影', '音樂製作'];
        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
