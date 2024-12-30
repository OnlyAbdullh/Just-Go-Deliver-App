<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\CartService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class RestockProducts
{
    /**
     * Create the event listener.
     */
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCancelled $event)
    {
        $caseStatements = [];
        $bindings = [];
        $ids = [];

        foreach ($event->orderProducts as $product) {
            $caseStatements[] = "WHEN id = ? THEN quantity + ?";
            $bindings[] = $product->store_product_id;
            $bindings[] = $product->quantity;
            $ids[] = $product->store_product_id;
        }

        $query = "UPDATE store_products
          SET quantity = CASE " . implode(' ', $caseStatements) . " END
          WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $bindings = array_merge($bindings, $ids);
        DB::statement($query, $bindings);

        $cart = $event->user->cart;
        $cartProducts = [];
        foreach ($event->orderProducts as $product) {
            $cartProducts[] = [
                'cart_id' => $cart->id,
                'store_product_id' => $product->store_product_id,
                'amount_needed' => $product->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($cartProducts)) {
            $this->cartService->addProductsToCartAgain($cart, $cartProducts);
        }
    }
}
