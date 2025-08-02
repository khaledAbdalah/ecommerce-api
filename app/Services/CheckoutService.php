<?php

namespace App\Services;

use App\Actions\AttachOrderItemsAction;
use App\Actions\ClearCartAction;
use App\Actions\CreateOrderAction;
use App\Actions\CreatePaymentAction;
use App\Actions\CreateShippingAddressAction;
use App\Actions\ProcessPaymentAction;
use App\DTOs\CheckoutData;
use App\Exceptions\EmptyCartException;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutService
{
    public function create (Request $request): array
    {
        $items = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        if ( $items->isEmpty() ) throw new EmptyCartException('Cart is empty');

        $total = $items->sum(fn ($item) => $item->total);

        $addresses = $request->user()->addresses;

        return [$items, $total, $addresses];
    }

    /**
     * @param CheckoutData $dto
     * @param Collection $items
     * @param float $total
     * @return array
     */
    public function store (CheckoutData $dto, Collection $items, float $total): array
    {

        // get shipping address or create
        $address = CreateShippingAddressAction::handle($dto);

        // create order
        $order = CreateOrderAction::handle($dto, $address, $total);

        // attach order items
        AttachOrderItemsAction::handle($items, $order);

        $payment = null;
        if ( $dto->paymentMethod === 'card' ) {
            $payment = ProcessPaymentAction::handle($total, $order, $dto);
        } else {
            $payment = CreatePaymentAction::handle($order, $total, $dto);
        }

        // clear cart
        ClearCartAction::handle($dto);

        return [$address, $order, $payment];
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function confirmPayment (Request $request): bool
    {
        $order = Order::find($request->order_id);
        if ( !$order->user_id === $request->user()->id ) {
            throw new AuthorizationException('Unauthorized');
        }
        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::retrieve($request->payment_intent_id);

        return $intent->status === 'succeeded' ? true : false;
    }
}