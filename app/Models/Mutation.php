<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = [
        'product_location_id',
        'user_id',
        'type',
        'quantity',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productLocation()
    {
        return $this->belongsTo(ProductLocation::class);
    }
}
