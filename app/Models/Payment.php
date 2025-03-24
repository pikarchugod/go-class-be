<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pay_method',
        'pay_status',
        'transaction_id',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
