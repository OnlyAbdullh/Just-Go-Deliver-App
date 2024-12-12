<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =  [
            'store_id' => $this->pivot->store_id,
            'store_name' => $this->store->name,
            'product_id' => $this->id,
            'product_name' => $this->name,
            'price' => $this->pivot->name,
            'quantity' => $this->pivot->quantity,
            'description' => $this->pivot->description,
            'main_image' => asset($this->pivot->main_image),
        ];

        if ($request->routeIs('products.show')) {
            $data['sub_images'] = $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => $image->image,
                    ];
                });
            });
        }

        return $data;
    }
}
