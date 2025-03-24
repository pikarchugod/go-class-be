<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\OrderItem;
use App\Models\Review;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'teacher_id',
        'category_id',
        'cover_image',
        'status',
    ];

    // 所屬老師
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // 所屬分類
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 課程的所有章節
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    // 課程的收藏 (多對多)
    // 也可用 favorites 表做 hasMany, 但常見做法是 belongsToMany
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'course_id', 'user_id');
    }

    // 訂單明細 (多筆 order_items)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'course_id');
    }

    // 評價
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
