<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_name',
        'attribute_value',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
