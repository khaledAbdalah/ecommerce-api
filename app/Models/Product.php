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
        'gallery' => 'array',
        'price' => 'decimal:2',
        'featured' => 'boolean',
    ];

    public function deleteThumbnail ()
    {
        if ( !empty($this->thumbnail) ) Storage::delete($this->thumbnail);
    }

    public function deleteGallery ()
    {
        if ( !empty($this->gallery) ) Storage::delete($this->gallery);
    }

    public function categories ()
    {
        return $this->belongsToMany(Category::class);
    }

    public function cartItems ()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems ()
    {
        return $this->hasMany(OrderItem::class);
    }
}