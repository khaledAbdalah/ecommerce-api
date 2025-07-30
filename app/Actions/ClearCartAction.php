<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Models\Cart;

class ClearCartAction
{
    /**
     * @param CheckoutData $dto
     * @return void
     */
    public static function handle(CheckoutData $dto): void
    {
        Cart::where('user_id', $dto->user->id)->delete();
    }
}
