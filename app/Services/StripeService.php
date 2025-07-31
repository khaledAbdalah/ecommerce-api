<?php

namespace App\Services;

use Exception;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService
{
    /**
     * @param float $amount
     * @param string $currency = 'use'
     */
    public static function createPaymentIntent(float $amount, string $currency = 'usd'): PaymentIntent
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            return PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true
                ]
            ]);
        } catch (Exception $e) {
            throw new Exception("Payment failed: {$e->getMessage()}");
        }
    }
}
