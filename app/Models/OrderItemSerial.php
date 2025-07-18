<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItemSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'serial_number',
        'price',
        'created_by'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
