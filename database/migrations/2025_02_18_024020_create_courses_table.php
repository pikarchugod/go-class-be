<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // 自動遞增的主鍵
            $table->string('title'); // 課程名稱
            $table->text('description')->nullable(); // 課程描述
            $table->decimal('price', 10, 2)->default(0); // 課程價格
            $table->unsignedBigInteger('teacher_id'); // 講師（關聯 users）
            $table->unsignedBigInteger('category_id')->nullable(); // 課程分類（關聯 categories）
            $table->string('cover_image')->nullable(); // 課程封面圖片
            $table->enum('status', ['draft', 'published'])->default('draft'); // 課程狀態
            $table->timestamps(); // 自動管理 created_at & updated_at

            // 設定外鍵
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
