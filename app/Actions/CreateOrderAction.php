<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Models\Order;
use App\Models\ShippingAddress;

class CreateOrderAction
{
    /**
     * @param CheckoutData $dto
     * @param ShippingAddress $address
     * @param int $total
     * @return Order 
     */
    public static function handle(CheckoutData $dto, ShippingAddress $address, int $total): Order
    {
        return Order::create([
            'user_id' => $dto->user->id,
            'shipping_address_id' => $address->id,
            'total' => $total,
        ]);
    }
}
