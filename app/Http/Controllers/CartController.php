<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        $total = $cartItems->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total' => $total,
                'items_count' => $cartItems->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);

            if ($product->stock < $request->quantity) {
                throw new \Exception("Only $product->stock items are available in stock.");
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;

                if ($product->stock < $newQuantity) {
                    throw new \Exception("Only $product->stock items are available in stock.");
                }

                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {

                $cartItem = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);
            }

            $cartItem->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'item' => $cartItem,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $id)
                ->with('product')
                ->first();

            if (!$cartItem) {
                throw new ModelNotFoundException('Item not found');
            }

            $product = $cartItem->product;

            if ($product->stock < $request->quantity) {
                throw new \Exception("Only $product->stock items are available in stock.");
            }

            $cartItem->update([
                'quantity' => $request->quantity,
            ]);

            $cartItem->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => [
                    'item' => $cartItem
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {

            $cartItem = Cart::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->first();

            if (!$cartItem) {
                throw new ModelNotFoundException('Item not found');
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
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
}
