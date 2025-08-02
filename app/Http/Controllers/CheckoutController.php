<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutData;
use App\Events\OrderPlacedEvent;
use App\Exceptions\EmptyCartException;
use App\Exceptions\LowStockException;
use App\Http\Requests\CheckoutStoreRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ShippingAddressResource;
use App\Models\Cart;
use App\Models\Payment;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutController extends Controller
{
    public function create (Request $request, CheckoutService $service)
    {
        try {
            [$items, $total, $addresses] = $service->create($request);
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => CartItemResource::collection($items),
                    'total' => number_format($total, 2),
                    'addresses' => ShippingAddressResource::collection($addresses),
                ],
            ]);
        } catch ( EmptyCartException $e ) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function store (CheckoutStoreRequest $request, CheckoutService $service)
    {
        try {
            $user = $request->user();

            $items = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            // throw exception if cart is empty
            if ( $items->isEmpty() ) {
                throw new EmptyCartException("cart is empty!");
            }

            // get items total
            $total = $items->sum(fn ($item) => $item->total);

            // create checkout data transfer object instance
            $dto = CheckoutData::create($user, $request->validated());

            DB::beginTransaction();

            // run checkout service
            [$address, $order, $payment] = $service->store($dto, $items, $total);

            DB::commit();

            event(new OrderPlacedEvent($order));

            if ( $dto->paymentMethod === 'card' ) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'require_payment' => true,
                    'data' => [
                        'order' => new OrderResource($order),
                        'items' => CartItemResource::collection($items),
                        'total' => number_format($total, 2),
                        'address' => new ShippingAddressResource($address),
                        'payment' => new PaymentResource($payment),
                        'payment_method' => $dto->paymentMethod,
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'require_payment' => false,
                'data' => [
                    'order' => new OrderResource($order),
                    'items' => CartItemResource::collection($items),
                    'total' => number_format($total, 2),
                    'address' => new ShippingAddressResource($address),
                    'payment_method' => $dto->paymentMethod,
                ]
            ]);
        } catch ( EmptyCartException $e ) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch ( LowStockException $e ) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch ( Throwable $e ) {
            DB::rollBack();
            return response()->unexpectedError($e);
        }
    }

    public function paymentCallback (Request $request, CheckoutService $service)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required',
                'order_id' => 'required',
            ]);

            $status = $service->confirmPayment($request);

            if ( $status === true ) {
                Payment::where('order_id', $request->order_id)->update([
                    'status' => 'paid',
                ]);
            }
            return response()->noContent();
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }
}