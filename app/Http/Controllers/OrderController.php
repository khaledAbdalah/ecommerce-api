<?php

namespace App\Http\Controllers;

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
            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                ]
            ]);
        } catch ( Throwable $e)
        {
            return response()->unexpectedError($e);
        }
    }

    public function show (Order $order)
    {
        try {
            $this->authorize('view', $order);
            $order->load(self::RELATIONS);
            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                ]
            ]);
        } catch ( Throwable $e){
            return response()->unexpectedError($e);
        }
    }

    public function update (Request $request, Order $order)
    {
        try {

            $this->authorize('update', $order);
            $request->validate([
                'status' => 'required|string',
            ]);

            $order->status = $request->status;
            $order->save();

            $order = $order->fresh(self::RELATIONS);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => [
                    'order' => $order,
                ],
            ]);

        } catch ( ValidationException $e ) {
            return response()->json([
                'success' => false,
                'error' => $e->errors(),
            ], 422);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function cancel (Request $request, Order $order)
    {
        try {
            $this->authorize('cancel', $order);
            $order->status = 'cancelled';
            $order->save();
            $order = $order->fresh(self::RELATIONS);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order' => $order,
                ],
            ]);

        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    public function destroy (Order $order)
    {
        try {
            $this->authorize('delete', $order);
            $order->delete();
            return response()->noContent();
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }
}