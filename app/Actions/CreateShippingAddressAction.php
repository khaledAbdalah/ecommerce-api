<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Models\ShippingAddress;

class CreateShippingAddressAction
{
    /**
     * @param CheckoutData $dto 
     * @return ShippingAddress
     */
    public static function handle(CheckoutData $dto): ShippingAddress
    {
        return  ShippingAddress::findOr($dto->addressId, callback: function () use ($dto) {
            return ShippingAddress::create([
                'address_line1' => $dto->addressLine1,
                'address_line2' => $dto->addressLine2,
                'city'          => $dto->city,
                'country'       => $dto->country,
                'phone'         => $dto->phone,
                'postal_code'   => $dto->postalCode,
                'state'         => $dto->state,
                'user_id'       => $dto->user->id,
            ]);
        });
    }
}
