<?php

namespace App\Http\Controllers;

use App\Actions\HandleErrorLoggingAction;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Concurrency;
use Throwable;

class ProductController extends Controller
{
    public function index ()
    {
        try {
            $products = Product::with('categories')->paginate(10);
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products
                ]
            ]);
        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }

    }

    public function create ()
    {
       try {
           $categories = Category::all(['id', 'name']);
           return response()->json([
               'success' => true,
               'data' => [
                   'categories' => $categories,
               ]
           ]);
       } catch ( Throwable $e ) {
           $message = 'Internal Server Error';
           Concurrency::defer(function () use ($e, $message) {
               HandleErrorLoggingAction::handle($e, $message);
           });

           return response()->json([
               'success' => false,
               'error' => $message,
           ], 500);
       }
    }


    public function store (ProductStoreRequest $request, ProductService $service)
    {
        try {

            $validated = $request->validated();
            $product = $service->create($request, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'data' => [
                    'product' => $product,
                ]
            ], 201);
        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }

    public function show (Product $product)
    {
        try {
            $product->load('categories');
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                ]
            ]);
        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }

    public function update (ProductUpdateRequest $request, Product $product, ProductService $service)
    {
        try {
            $validated = $request->validated();
            $product = $service->update($request, $validated, $product );

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'product' => $product,
                ]
            ]);
        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }


    public function destroy (Product $product, ProductService $service)
    {
        try {
            $this->authorize('delete', $product);
            $service->delete($product);
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch ( Throwable $e ) {
            $message = 'Internal Server Error';
            Concurrency::defer(function () use ($e, $message) {
                HandleErrorLoggingAction::handle($e, $message);
            });

            return response()->json([
                'success' => false,
                'error' => $message,
            ], 500);
        }
    }
}