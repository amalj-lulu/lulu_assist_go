<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sap_product_id',
        'product_name',
        'ean_number',
        'material_category',
    ];
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
