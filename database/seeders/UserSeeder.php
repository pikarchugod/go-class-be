<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 建立一個老師帳號
        User::create([
            'name' => 'John Doe',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher', // 確保角色是老師
        ]);
        // 建立 3 位學生
        User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }
}
