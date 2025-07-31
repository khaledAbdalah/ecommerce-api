<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Models\Order;
use App\Models\Payment;

class CreatePaymentAction
{
    /**
     * @param Order $order
     * @param float $amount
     * @param CheckoutData $dto
     * @return Payment
     */
    public static function handle(Order $order, float $amount, CheckoutData $dto): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $dto->paymentMethod,
        ]);
    }
}
