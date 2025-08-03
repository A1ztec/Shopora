<?php

namespace App\Models;

use App\Enum\Product\SaleType;
use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    protected $fillable = [
        'product_id',
        'sale_type',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'sale_type' => SaleType::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
