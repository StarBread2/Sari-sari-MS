<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'selling_price',
        'effective_to',
    ];

    protected $casts = [
        'effective_to' => 'datetime',
        'selling_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}