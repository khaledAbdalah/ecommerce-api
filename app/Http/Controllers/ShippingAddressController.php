<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingAddressStoreRequest;
use App\Models\ShippingAddress;
use Exception;

class ShippingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ()
    {
        $this->authorize('viewAny', ShippingAddress::class);
        $shippingAddresses = ShippingAddress::with('user')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => [
                'shippingAddresses' => $shippingAddresses
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (ShippingAddressStoreRequest $request)
    {
        try {
            $address = auth()->user()->addresses()->create($request->validated());
            return response()->json([
                'success' => true,
                'data' => [
                    'shippingAddress' => $address
                ]
            ]);
        } catch ( Exception $exception ) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show (ShippingAddress $shippingAddress)
    {
        $this->authorize('view', $shippingAddress);
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'shippingAddress' => $shippingAddress
                ]
            ]);
        } catch ( Exception $exception ) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 400);
        }
    }

    public function userAddresses ()
    {
        try {
            $addresses = auth()->user()->addresses;
            return response()->json([
                'success' => true,
                'data' => [
                    'addresses' => $addresses
                ]
            ]);
        } catch ( Exception $exception ) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (ShippingAddressStoreRequest $request, ShippingAddress $shippingAddress)
    {
        try {
            $this->authorize('update', $shippingAddress);
            $shippingAddress->update($request->validated());
            $address = $shippingAddress->fresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'shippingAddress' => $address
                ]
            ]);
        } catch ( Exception $exception ) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (ShippingAddress $shippingAddress)
    {
        $this->authorize('delete', $shippingAddress);
        $shippingAddress->delete();
        return response()->noContent();
    }
}