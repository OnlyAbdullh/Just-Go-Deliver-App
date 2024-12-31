<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'store_product_id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'product_details' => new ProductResource($this->whenLoaded('storeProduct')),
        ];
    }
}
