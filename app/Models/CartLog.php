<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartLog extends Model
{
    protected $fillable = ['cart_id', 'cart_item_id', 'action', 'details', 'performed_by'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }
}
