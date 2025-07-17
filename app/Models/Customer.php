<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mobile'];

    public $incrementing = false;       // UUIDs are not auto-incrementing
    protected $keyType = 'string';      // UUID is stored as a string

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    public function cart()
    {
        return $this->hasOne(Cart::class)->where('status', 'active');
    }
}
