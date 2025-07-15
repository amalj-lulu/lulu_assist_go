<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'ean_number',
        'product_name',
        'quantity',
        'delivery_type',
        'created_by',
        'is_deleted'
    ];

    public function serials(): HasMany
    {
        return $this->hasMany(CartItemSerial::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
