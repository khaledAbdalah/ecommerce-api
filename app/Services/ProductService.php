<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductService
{
    public function create (Request $request, array $data): Product
    {
        // store thumbnail and gallery
        $data['thumbnail'] = $this->uploadThumbnail($request);
        $data['gallery'] = $this->uploadGallery($request);
        $product = Product::create($data);

        if ( !empty($data['categories']) ) {
            $product->categories()->attach($data['categories']);
        }

        return $product->load('categories');
    }

    public function update (Request $request, array $data, Product $product): Product
    {
        // store thumbnail and gallery
        if ( $request->hasFile('thumbnail') ) {
            $data['thumbnail'] = $this->uploadThumbnail($request, $product, true);
        }

        if ( $request->hasFile('gallery') ) {
            $data['gallery']  = $this->uploadGallery($request, $product);
        }

        // fill data
        $product->fill($data)->save();

        if ( !empty($data['categories']) ) {
            $product->categories()->sync($data['categories']);
        }

        return  $product->fresh('categories');
    }

    protected function uploadGallery (Request $request): array
    {
        $gallery = [];
        foreach ( $request->file('gallery') as $image ) {
            $gallery[] = $image->store('products/galleries');
        }
        return $gallery;
    }

    protected function uploadThumbnail (Request $request, ?Product $product = null, $deleteThumbnail = false): string
    {
        if($deleteThumbnail && $product) $product->deleteThumbnail();
        return $request->file('thumbnail')->store('products/thumbnails');
    }

    public function delete (Product $product): void
    {
        $product->deleteThumbnail();
        $product->deleteGallery();
        $product->delete();
    }
}