<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReorderLevel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'min_stock',
        'reorder_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}