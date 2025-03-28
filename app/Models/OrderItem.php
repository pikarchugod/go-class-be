<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Course;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'course_id',
        'price',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
