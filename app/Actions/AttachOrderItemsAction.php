<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Exceptions\LowStockException;
use Illuminate\Database\Eloquent\Collection;

class AttachOrderItemsAction
{
    /**
     * @param Collection $items
     * @param Order $order
     * @return void
     */
    public static function handle(Collection $items, Order $order): void
    {
        foreach ($items as $item) {
            if (!$item->product || $item->product->stock < $item->quantity) {
                throw new LowStockException("Only {$item->product->stock} items are available in stock.");
            }
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'total' => $item->total,
            ]);

            $item->product->decrement('stock', $item->quantity);
        }
    }
}
