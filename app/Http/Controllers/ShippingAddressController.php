<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingAddressStoreRequest;
use App\Models\ShippingAddress;
use Throwable;

class ShippingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ()
    {
        try {
            $this->authorize('viewAny', ShippingAddress::class);
            $shippingAddresses = ShippingAddress::with('user')->paginate(10);
            return response()->json([
                'success' => true,
                'data' => [
                    'shippingAddresses' => $shippingAddresses
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
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
        } catch ( Throwable $exception ) {
            return response()->unexpectedError($exception);
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
        } catch ( Throwable $exception ) {
            return response()->unexpectedError($exception);
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
        } catch ( Throwable $exception ) {
            return response()->unexpectedError($exception);
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
        } catch ( Throwable $exception ) {
            return response()->unexpectedError($exception);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (ShippingAddress $shippingAddress)
    {
        try {
            $this->authorize('delete', $shippingAddress);
            $shippingAddress->delete();
            return response()->noContent();
        } catch ( Throwable $exception ) {
            return response()->unexpectedError($exception);
        }
    }
}