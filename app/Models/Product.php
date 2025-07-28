<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function deleteThumbnail()
    {
        Storage::delete($this->thumbnail);
    }

    public function deleteGallery()
    {
        Storage::delete($this->gallery);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }
}
