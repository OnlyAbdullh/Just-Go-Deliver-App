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

    /**
     * @OASchema(
     *     schema="ProductDetailResource",
     *     type="object",
     *
     *     @OAProperty(property="store_product_id", type="integer", example=1),
     *     @OAProperty(property="product_id", type="integer", example=101),
     *     @OAProperty(property="product_name", type="string", example="Sample Product"),
     *     @OAProperty(property="category_name", type="string", example="Electronics"),
     *     @OAProperty(property="main_image", type="string", format="uri", example="https://example.com/storage/images/sample.jpg"),
     *     @OAProperty(property="price", type="number", format="float", example=99.99),
     *     @OAProperty(property="quantity", type="integer", example=10),
     *     @OAProperty(property="description", type="string", example="This is a sample product description.")
     * )
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
