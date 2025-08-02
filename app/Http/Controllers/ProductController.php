<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Throwable;

class ProductController extends Controller
{
    public function index ()
    {
        try {
            $products = Product::with('categories')
                ->paginate(10)
                ->toResourceCollection();
            return response()->json([
                'success' => true,
                'data' => [$products]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }

    }

    public function create ()
    {
        try {
            $this->authorize('create', Product::class);
            $categories = Category::all(['id', 'name']);
            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function store (ProductStoreRequest $request, ProductService $service)
    {
        try {
            $validated = $request->validated();
            $product = $service->create($request, $validated);

            return response()->json([
                'success' => true,
                'message' => 'ProductCollection added successfully',
                'data' => [
                    'product' => new ProductResource($product),
                ]
            ], 201);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function show (Product $product)
    {
        try {
            $product->load('categories');
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => new ProductResource($product),
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function update (ProductUpdateRequest $request, Product $product, ProductService $service)
    {
        try {
            $validated = $request->validated();
            $product = $service->update($request, $validated, $product);

            return response()->json([
                'success' => true,
                'message' => 'ProductCollection updated successfully',
                'data' => [
                    'product' => new ProductResource($product),
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function destroy (Product $product, ProductService $service)
    {
        try {
            $this->authorize('delete', $product);
            $service->delete($product);
            return response()->json([
                'success' => true,
                'message' => 'ProductCollection deleted successfully'
            ]);

        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }
}