<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'created_by'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function serials()
    {
        return $this->hasMany(OrderItemSerial::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
