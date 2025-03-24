<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Models\Course;
use App\Models\Order;
use App\Models\ProgressTrack;
use App\Models\Review;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'social_id',
        'provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'social_id',
        'provider',
    ];

    // 角色可為 student, teacher, admin
    // casts 可根據需要添加

    // 老師擁有的課程 (teacher_id)
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // 訂單
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // 購物車 (一個 user 可有多筆 cart item)
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // 收藏 (多對多) → favorites 表為中介
    // 但你目前 Favorite 是一張獨立表, 也可以做 belongsToMany
    // 現在做法: user->belongsToMany(course) through favorites
    public function favorites()
    {
        return $this->belongsToMany(Course::class, 'favorites', 'user_id', 'course_id');
    }

    // 進度追蹤
    public function progressTracks()
    {
        return $this->hasMany(ProgressTrack::class);
    }

    // 評價
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
