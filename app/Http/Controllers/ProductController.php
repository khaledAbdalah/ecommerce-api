<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'products' => Product::with('categories')->paginate(10)
            ]
        ]);
    }

    
    public function create()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::all(['id', 'name']),
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated =  $request->validate([
                'name' => 'required|string|max:255',
                'short_description' => 'required|string',
                'description' => 'required|string',
                'thumbnail' => 'required|image|mimes:png,jpg,jpeg,webp',
                'gallery' => 'required|array|min:4',
                'gallery.*' => 'image|mimes:png,jpg,jpeg,webp',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'status' => 'required|in:published,draft',
                'featured' => 'required|boolean',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id'
            ]);

            // store thumbnail and gallery 
            $thumbnail = $request->thumbnail->store('products/thumbnails');

            $gallery = [];
            foreach ($request->gallery as $image) {
                $gallery[] = $image->store('products/galleries');
            }

            $jsonGallery = json_encode($gallery);

            $validated['thumbnail'] = $thumbnail;
            $validated['gallery'] = $jsonGallery;

            $product = Product::create($validated);

            if (!empty($validated['categories'])) {
                $product->categories()->attach($validated['categories']);
            }

            $product->load('categories');

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'data' => [
                    'product' => $product,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            if (!$product = Product::with('categories')->find($id)) {
                throw new ModelNotFoundException('Product not found!');
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            if (!$product = Product::find($id)) throw new ModelNotFoundException('Product not found');

            $validated =  $request->validate([
                'name' => 'required|string|max:255',
                'short_description' => 'required|string',
                'description' => 'required|string',
                'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,webp',
                'gallery' => 'nullable|array',
                'gallery.*' => 'image|mimes:png,jpg,jpeg,webp',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'status' => 'required|in:published,draft',
                'featured' => 'required|boolean',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id'
            ]);

            // store thumbnail and gallery 
            if ($request->hasFile('thumbnail')) {
                $product->deleteThumbnail();
                $validated['thumbnail'] = $request->file('thumbnail')->store('products/thumbnails');
            }

            if ($request->hasFile('gallery') && is_array($request->file('gallery'))) {
                $gallery = [];
                foreach ($request->file('gallery') as $image) {
                    $gallery[] = $image->store('products/galleries');
                }

                $validated['gallery'] = json_encode($gallery);
            }

            $product->update($validated);

            if(!empty($validated['categories'])){
                $product->categories()->sync($validated['categories']);
            }

            $product->load('categories');

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'product' => $product,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            if (!$product = Product::find($id)) throw new ModelNotFoundException('Product not found');

            $product->deleteThumbnail();

            $product->deleteGallery();

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Internal Server Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
