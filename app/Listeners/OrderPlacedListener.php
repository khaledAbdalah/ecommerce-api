<?php

namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPlacedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct () {}

    /**
     * Handle the event.
     */
    public function handle (OrderPlacedEvent $event): void
    {
        $order = $event->order;
        $order->load(['payment', 'shippingAddress', 'items.product', 'user']);
        $order->user->notify(new OrderPlacedNotification($order));
    }
}