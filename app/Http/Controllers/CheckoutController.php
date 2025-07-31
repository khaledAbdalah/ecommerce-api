<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutData;
use App\Exceptions\EmptyCartException;
use App\Exceptions\LowStockException;
use App\Http\Requests\CheckoutStoreRequest;
use App\Models\Cart;
use App\Models\Payment;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // get items total
            $total = $items->sum(fn($item) => $item->total);

            // create checkout data transfair object instance
            $dto = CheckoutData::create($user, $request->validated());

            DB::beginTransaction();

            // run checkout service
            $handle = CheckoutService::checkout($dto, $items, $total);

            DB::commit();

            if ($dto->paymentMethod === 'card') {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'require_payment' => true,
                    'data' => [
                        'order'          => $handle['order'],
                        'items'          => $items,
                        'total'          => $total,
                        'address'        => $handle['address'],
                        'payment'        => $handle['payment'],
                        'payment_method' => $dto->paymentMethod,
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'require_payment' => false,
                'data' => [
                    'order'          => $handle['order'],
                    'items'          => $items,
                    'total'          => $total,
                    'address'        => $handle['address'],
                    'payment_method' => $dto->paymentMethod,
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

    public function paymentCallback(Request $request)
    {
        try {

            $request->validate([
                'payment_intent_id' => 'required',
                'order_id' => 'required',
            ]);

            $status = CheckoutService::confirmPayment($request);

            if ($status === true) {
                $pay = Payment::where('order_id', $request->order_id)->update([
                    'status' => 'paid',
                ]);
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => $status,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
