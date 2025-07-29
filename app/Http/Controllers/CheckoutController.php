<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function create(Request $request)
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
        $total = $items->sum('item_total');

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
    }
}
