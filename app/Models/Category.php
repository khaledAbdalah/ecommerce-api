<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = ['name', 'description', 'thumbnail'];

    public function deleteThumbnail ()
    {
        if ( !empty($this->thumbnail) ) Storage::delete($this->thumbnail);
    }

    public function products ()
    {
        return $this->belongsToMany(Product::class);
    }
}