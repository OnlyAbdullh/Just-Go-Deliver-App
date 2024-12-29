<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Store_Product;
use Illuminate\Support\Facades\DB;

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
        $caseStatements = [];
        $bindings = [];
        $ids = [];

        foreach ($event->orderProducts as $product) {
            $caseStatements[] = "WHEN id = ? THEN quantity - ?";
            $bindings[] = $product['store_product_id'];
            $bindings[] = $product['quantity'];
            $ids[] = $product['store_product_id'];
        }

        $query = "UPDATE store_products
              SET quantity = CASE " . implode(' ', $caseStatements) . " END
              WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";

        $bindings = array_merge($bindings, $ids);

        DB::statement($query, $bindings);
    }
}
