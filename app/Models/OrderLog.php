<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_number',
        'cart_id',
        'action',
        'details',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
