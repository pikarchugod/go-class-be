<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Payment;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_method',
        'trade_no',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 一個訂單對應一筆支付紀錄 (若只有一次支付)
    // 若有多次支付嘗試，則改為 hasMany
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
