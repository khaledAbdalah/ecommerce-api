<?php

namespace App\Services;

use App\Actions\CreatePaymentAction;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use App\DTOs\CheckoutData;
use Illuminate\Http\Request;
use App\Actions\ClearCartAction;
use App\Actions\CreateOrderAction;
use App\Actions\ProcessPaymentAction;
use App\Actions\AttachOrderItemsAction;
use App\Actions\CreateShippingAddressAction;
use Illuminate\Database\Eloquent\Collection;

class CheckoutService
{
   /**
    * @param CheckoutData $dto
    * @param Collection $items
    * @param float $total
    * @return array
    */
   public static function checkout(CheckoutData $dto, Collection $items, float $total): array
   {

      // get shipping address or create
      $address = CreateShippingAddressAction::handle($dto);

      // create order
      $order = CreateOrderAction::handle($dto, $address, $total);

      // attach order items
      AttachOrderItemsAction::handle($items, $order);

      $payment = null;
      if ($dto->paymentMethod === 'card') {
         $payment = ProcessPaymentAction::handle($total, $order, $dto);
      }else{
          $payment = CreatePaymentAction::handle($order, $total, $dto);
      }

      // clear cart
      ClearCartAction::handle($dto);

      return [
         'address'   => $address,
         'order'     => $order,
         'payment'   => $payment
      ];
   }

   /**
    * @param Request $request
    * @return bool
    */
   public static function confirmPayment(Request $request): bool
   {
      Stripe::setApiKey(config('services.stripe.secret'));

      $intent = PaymentIntent::retrieve($request->payment_intent_id);

      return $intent->status = 'succeeded' ? true : false;
   }
}
