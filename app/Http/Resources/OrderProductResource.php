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
    protected $isFavorite;

    public function __construct($resource, $isFavorite = 0)
    {
        parent::__construct($resource);
        $this->isFavorite = $isFavorite;
    }

    public function toArray(Request $request): array
    {

        return [
            'store_product_id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'product_details' => $this->whenLoaded('storeProduct', function () {
                return new ProductResource($this->storeProduct, $this->isFavorite);
            }),
        ];
    }
}
