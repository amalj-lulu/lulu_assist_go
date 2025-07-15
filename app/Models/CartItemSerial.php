<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemSerial extends Model
{
    protected $fillable = ['cart_item_id', 'serial_number','created_by','is_deleted'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }
}
