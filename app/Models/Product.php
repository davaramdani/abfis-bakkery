<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $primaryKey = 'id_product';
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
                    ->withPivot('quantity', 'price_at_order')
                    ->withTimestamps();
    }
}