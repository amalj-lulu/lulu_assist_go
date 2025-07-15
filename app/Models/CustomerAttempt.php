<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAttempt extends Model
{
    protected $fillable = [
        'customer_id',
        'mobile',
        'action',
        'performed_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
