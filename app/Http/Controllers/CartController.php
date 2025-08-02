<?php

namespace App\Http\Controllers;

use App\Exceptions\LowStockException;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => []
                ]
            ]);
        }

        $total = $items->sum(fn($item) => $item->total);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $total,
                'items_count' => $items->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Cart::class);
            $user = $request->user();
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);
            $this->ensureStockAvailable($product, $request->quantity);

            $item = Cart::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($item) {
                $this->ensureStockAvailable($product, $request->quantity);

                $item->quantity = $request->quantity;
                $item->save();
                $item = $item->fresh('product');

            } else {
                $item = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);

                $item = $item->load(['product']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'item' => $item,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch ( LowStockException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch ( Throwable $e){
            return response()->unexpectedError($e);
        }
    }



    public function update(Request $request, Cart $item)
    {
        try {
            $this->authorize('update', $item);
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $item->load(['product']);
            $product = $item->product;
            $this->ensureStockAvailable($product, $request->quantity);

            $item->quantity = $request->quantity;
            $item->save();
            $item = $item->fresh(['product']);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => [
                    'item' => $item
                ]
            ]);
        }  catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch ( Throwable $e) {
            return response()->unexpectedError($e);
        }
    }

    public function destroy(Request $request, Cart $item)
    {
        try {
            $this->authorize('delete', $item);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch ( Throwable $e) {
            return response()->unexpectedError($e);
        }
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    public function count(Request $request)
    {
        $count = Cart::where('user_id', $request->user()->id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    protected function ensureStockAvailable(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new LowStockException("Only {$product->stock} items are available in stock.");
        }
    }
}