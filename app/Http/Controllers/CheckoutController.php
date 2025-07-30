<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutData;
use App\Exceptions\EmptyCartException;
use App\Exceptions\LowStockException;
use App\Http\Requests\CheckoutStoreRequest;
use App\Models\Cart;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        try {
            $items = Cart::with('product')
                ->where('user_id', $request->user()->id)
                ->get();

            if ($items->isEmpty()) throw new EmptyCartException('Cart is empty');


            $total = $items->sum(fn($item) => $item->total);

            $addresses = $request->user()->addresses;

            if ($addresses->isEmpty()) {
                $addresses = [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'total' => $total,
                    'addresses' => $addresses,
                ],
            ]);
        } catch (EmptyCartException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }


    public function store(CheckoutStoreRequest $request)
    {
        try {

            // get user
            $user = $request->user();

            // get cart items
            $items = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            // throw exception if cart is empty
            if ($items->isEmpty()) {
                throw new EmptyCartException("cart is empty!");
            }

            DB::beginTransaction();

            // create checkout data object instance
            $dto = CheckoutData::create($user, $request->validated());

            // run checkout service
            $handle = CheckoutService::checkout($dto, $items);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $handle['order'],
                    'items' => $items,
                    'total' => $handle['total'],
                    'address' => $handle['address'],
                    'payment_method' => 'Cash On Delivery', // for testing
                ]
            ]);
        } catch (EmptyCartException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (LowStockException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
