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

        return $this->success([
            'items' => CartItemResource::collection($items),
            'total' => number_format($total, 2),
            'items_count' => $items->count()
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

            return $this->success(['item' => new CartItemResource($item)], 'Item added to cart successfully'
                , 201);
        } catch ( ModelNotFoundException $e ) {
            return $this->error($e->getMessage(), 404);
        } catch ( LowStockException $e ) {
            return $this->error($e->getMessage());
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
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

            return $this->success(['item' => new CartItemResource($item)], 'Item updated successfully');
        } catch ( LowStockException $e ) {
            return $this->error($e->getMessage());
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function destroy (Request $request, Cart $item)
    {
        $this->authorize('delete', $item);
        try {
            $item->delete();

            return $this->success(message: 'Item deleted successfully');
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function clear (Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();
        return $this->success(message: 'Cart cleared successfully');
    }

    public function count (Request $request)
    {
        $count = Cart::where('user_id', $request->user()->id)->count();
        return $this->success(['count' => $count]);
    }

    protected function ensureStockAvailable (Product $product, int $quantity): void
    {
        if ( $product->stock < $quantity ) {
            throw new LowStockException("Only {$product->stock} items are available in stock.");
        }
    }
}