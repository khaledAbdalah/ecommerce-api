<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Models\Order;
use App\Services\StripeService;

class ProcessPaymentAction
{
    /**
     * @param float total
     * @param Order $order
     * @param CheckoutData $dto
     * @return array
     */
    public static function handle(float $total, Order $order, CheckoutData $dto): array
    {
        $pay = StripeService::createPaymentIntent($total);

        // create payment model
        CreatePaymentAction::handle($order, $total, $dto);

        return [
            'client_secret' => $pay->client_secret,
            'payment_id'    => $pay->id
        ];
    }
}
