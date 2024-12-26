<?php

namespace App\Listeners;

use App\Events\NotifyQuantityAvailable;
use App\Models\CartProduct;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotification implements ShouldQueue, ShouldHandleEventsAfterCommit
{
    /**
     * Create the event listener.
     */
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Handle the event.
     */
    public function handle(NotifyQuantityAvailable $event)
    {
        $cartProducts = CartProduct::query()
            ->where('store_product_id', $event->storeProductId)
            ->whereHas('storeProduct', function ($query) {
                $query->whereColumn('quantity', '>=', 'amount_needed');
            })
            ->get();

        foreach ($cartProducts as $cartProduct) {
            $user = $cartProduct->cart->user;

            $this->sendNotifications(
                $user,
                "Product Available",
                "The product '{$cartProduct->storeProduct->product->name}' is now available at store '{$cartProduct->storeProduct->store->name}' in the quantity you requested!"
            );
        }
    }

    private function sendNotifications(User $user, $title, $body)
    {
        $fcmTokens = $user->deviceTokens()->pluck('token')->toArray();

        if (empty($fcmTokens)) {
            return;
        }
        $this->fcmService->sendNotification($fcmTokens, $title, $body);
    }
}
