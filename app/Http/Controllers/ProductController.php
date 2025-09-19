<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\CategoryResource;
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
            return $this->success($products);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }

    }

    public function create ()
    {
        $this->authorize('create', Product::class);
        try {
            $categories = CategoryResource::collection(Category::all(['id', 'name']));
            return $this->success(['categories' => $categories]);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function store (ProductStoreRequest $request, ProductService $service)
    {
        try {
            $validated = $request->validated();
            $product = $service->create($request, $validated);

            return $this->success(['product' => new ProductResource($product)], 'ProductCollection added successfully', 201);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function show (Product $product)
    {
        try {
            $product->load('categories');
            return $this->success(['product' => new ProductResource($product)]);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function update (ProductUpdateRequest $request, Product $product, ProductService $service)
    {
        try {
            $validated = $request->validated();
            $product = $service->update($request, $validated, $product);

            return $this->success(['product' => new ProductResource($product)], 'ProductCollection updated successfully');
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function destroy (Product $product, ProductService $service)
    {
        $this->authorize('delete', $product);
        try {
            $service->delete($product);
            return $this->success(message: 'ProductCollection deleted successfully');

        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }
}