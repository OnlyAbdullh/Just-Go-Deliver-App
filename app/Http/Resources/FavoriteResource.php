<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mainUrl = Storage::url($this->main_image);
        $lang = app()->getLocale();

        $storeName = 'name_' . $lang;
        $productName = 'name_' . $lang;
        $categoryName = 'name_' . $lang;
        $description = 'description_' . $lang;
        return [
            'store_id' => $this->store_id ?? null,
            'store_name' => $this->$storeName ?? null,
            'product_id' => $this->product_id ?? null,
            'product_name' => $this->$productName ?? null,
            'category_id' => $this->category_id ?? null,
            'category_name' => $this->$categoryName ?? null,
            'price' => $this->price ?? null,
            'quantity' => $this->quantity ?? null,
            'description' => $this->$description ?? null,
            'is_favorite' => 1,
            'main_image' => asset($mainUrl),
        ];
    }
}
