<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'date',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
