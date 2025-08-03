<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enum\Order\OrderStatus;
use App\Enum\Order\PaymentMethod;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'cart_id',
        'total_amount',
        'address',
        'status',
        'payment_method',
        'coupon_id',
        'discount_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
