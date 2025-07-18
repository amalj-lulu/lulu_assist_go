<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'is_warehouse'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
