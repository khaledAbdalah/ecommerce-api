<?php

namespace App\Services;

use App\Actions\AttachOrderItemsAction;
use App\Actions\ClearCartAction;
use App\Actions\CreateOrderAction;
use App\Actions\CreateShippingAddressAction;
use App\DTOs\CheckoutData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
   /**
    * @param CheckoutData $dto
    * @param Collection $items
    * @return array
    */
   public static function checkout(CheckoutData $dto, Collection $items): array
   {

      // get items total
      $total = $items->sum(fn($item) => $item->total);

      // get shipping address or create
      $address = CreateShippingAddressAction::handle($dto);

      // create order
      $order = CreateOrderAction::handle($dto, $address, $total);

      // attach order items 
      AttachOrderItemsAction::handle($items, $order);

      // clear cart
      ClearCartAction::handle($dto);

      return [
         'total'     => $total,
         'address'   => $address,
         'order'     => $order,
      ];
   }
}
