<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    const RELATIONS = ['user', 'items.product', 'payment', 'shippingAddress'];

    public function index ()
    {
        try {
            $this->authorize('view-any', Order::class);
            $orders = Order::with(self::RELATIONS)->paginate(10);
            return $this->success(new OrderCollection($orders));
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function show (Order $order)
    {
        $this->authorize('view', $order);
        try {
            $order->load(self::RELATIONS);
            return $this->success(['order' => new OrderResource($order)]);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function update (Request $request, Order $order)
    {
        $this->authorize('update', $order);
        try {
            $request->validate([
                'status' => 'required|string',
            ]);

            $order->status = $request->status;
            $order->save();

            $order = $order->fresh(self::RELATIONS);

            return $this->success(['order' => new OrderResource($order)], 'Order updated successfully');

        } catch ( ValidationException $e ) {
            return $this->error($e->getMessage(), 422);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function cancel (Request $request, Order $order)
    {
        $this->authorize('cancel', $order);
        try {
            $order->status = 'cancelled';
            $order->save();
            $order = $order->fresh(self::RELATIONS);

            return $this->success(['order' => new OrderResource($order)], 'Order cancelled successfully');

        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    public function destroy (Order $order)
    {
        $this->authorize('delete', $order);
        try {
            $order->delete();
            return response()->noContent();
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }
}