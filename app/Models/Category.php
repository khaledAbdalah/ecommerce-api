<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = ['name' , 'description' , 'thumbnail'];

    public function deleteThumbnail()
    {
        Storage::delete($this->thumbanil);
    }
}
