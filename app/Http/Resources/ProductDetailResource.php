<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'store_product_id' => $this->store_product_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'category_name' => $this->category_name,
            'main_image' => asset(Storage::url($this->main_image)),
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->description,
        ];
    }
}
