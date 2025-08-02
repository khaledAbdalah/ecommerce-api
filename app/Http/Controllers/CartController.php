<?php

namespace App\Http\Controllers;

use App\Exceptions\LowStockException;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class CartController extends Controller
{
    public function index (Request $request)
    {
        $items = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        $total = $items->sum(fn ($item) => $item->total);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => CartItemResource::collection($items),
                'total' => number_format($total, 2),
                'items_count' => $items->count()
            ]
        ]);
    }

    public function store (CartStoreRequest $request)
    {
        try {
            $user = $request->user();
            $product = Product::findOrFail($request->product_id);

            $item = Cart::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ( $item ) {
                $this->ensureStockAvailable($product, $request->quantity);
                $item->quantity += $request->quantity;
                $item->save();
                $item = $item->fresh('product');

            } else {
                $this->ensureStockAvailable($product, $request->quantity);
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
                    'item' => new CartItemResource($item),
                ]
            ], 201);
        } catch ( ModelNotFoundException $e ) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        } catch ( LowStockException $e ) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function update (CartUpdateRequest $request, Cart $item)
    {
        try {
            $item->load(['product']);
            $product = $item->product;
            $this->ensureStockAvailable($product, $request->quantity);

            $item->fill(['quantity' => $request->quantity])->save();
            $item = $item->fresh(['product']);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => [
                    'item' => new CartItemResource($item),
                ]
            ]);
        } catch ( LowStockException $e ) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function destroy (Request $request, Cart $item)
    {
        $this->authorize('delete', $item);
        try {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function clear (Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    public function count (Request $request)
    {
        $count = Cart::where('user_id', $request->user()->id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    protected function ensureStockAvailable (Product $product, int $quantity): void
    {
        if ( $product->stock < $quantity ) {
            throw new LowStockException("Only {$product->stock} items are available in stock.");
        }
    }
}