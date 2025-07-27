<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'short_description',
        'description',
        'thumbnail',
        'gallery',
        'price',
        'stock',
        'status',
        'featured',
    ];

    protected $casts = [
        'gallery' => 'array'
    ];
}
