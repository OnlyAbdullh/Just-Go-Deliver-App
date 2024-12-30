<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
     *
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
        $mainUrl = Storage::url($this->main_image);

        $lang = app()->getLocale();

        $productName = 'name_' . $lang;
        $storeName = 'name_' . $lang;
        $description = 'description_' . $lang;
        $categoryName = 'name_' . $lang;

        $data = [
            'store_id' => $this->store_id,
            'store_name' => $this->store->$storeName ?? null,
            'product_id' => $this->product_id,
            'product_name' => $this->product->$productName ?? null,
            'category_id' => $this->product->category->id,
            'category_name' => $this->product->category->$categoryName,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->$description,
            'is_favorite' => $this->product->favoritedByUsers->isNotEmpty() ? 1 : 0,
            'main_image' => asset($mainUrl),
        ];

        if ($request->routeIs('products.show')) {

            $quantityInCart = DB::table('cart_products')
                ->join('carts', 'carts.id', '=', 'cart_products.cart_id')
                ->where('cart_products.store_product_id', $this->id)
                ->where('carts.user_id', auth()->id())
                ->sum('cart_products.amount_needed');

            $data['isInCart'] = $quantityInCart === 0 ? 0 : 1;
            $data['quantityInCart'] = (int) $quantityInCart;

            $data['sub_images'] = $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => asset(Storage::url($image->image)),
                    ];
                });
            });
        }

        return $data;
    }
}
