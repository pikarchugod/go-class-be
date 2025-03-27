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
        $categories = ['圍棋入門', '圍棋初階', '圍棋進階', '圍棋中階', '圍棋高階'];
        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
