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

    /**
     * @OA\Schema(
     *     schema="ProductResource",
     *     type="object",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="store_id",
     *         type="integer",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="store_name",
     *         type="string",
     *         example="Aydiiiii"
     *     ),
     *     @OA\Property(
     *         property="product_id",
     *         type="integer",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="product_name",
     *         type="string",
     *         example="Product Name"
     *     ),
     *     @OA\Property(
     *         property="price",
     *         type="string",
     *         example="100.00"
     *     ),
     *     @OA\Property(
     *         property="quantity",
     *         type="integer",
     *         example=50
     *     ),
     *     @OA\Property(
     *         property="is_fav",
     *         type="boolean",
     *         example=true
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         example="Product description for this store"
     *     ),
     *     @OA\Property(
     *         property="main_image",
     *         type="string",
     *         example="path/to/main_image.jpg"
     *     )
     * )
     */

    public function toArray(Request $request): array
    {
        $data =  [
            'store_id' => $this->store_id,
            'store_name' => $this->store->name ?? null,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? null,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'main_image' => asset($this->main_image),
        ];

        if ($request->routeIs('products.show')) {
            $data['sub_images'] = $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => asset($image->image),
                    ];
                });
            });
        }

        return $data;
    }
}
