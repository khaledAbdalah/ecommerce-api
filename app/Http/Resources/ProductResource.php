<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'thumbnail'         => asset('storage/' . $this->thumbnail),
            'gallery'           => collect($this->gallery)->map(fn($image) => asset('storage/' . $image)),
            'price'             => $this->price,
            'stock'             => $this->stock,
            'status'            => $this->status,
            'featured'          => $this->featured,
            'categories'        => $this->whenLoaded('categories'),
        ];
    }
}
