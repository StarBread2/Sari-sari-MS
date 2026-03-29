<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'contact_number',
        'address',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}