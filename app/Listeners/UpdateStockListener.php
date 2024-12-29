<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Store_Product;
class UpdateStockListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event)
    {
        foreach ($event->orderProducts as $product) {
            Store_Product::where('id', $product['store_product_id'])
                ->decrement('quantity', $product['quantity']);
        }
    }
}
