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

        return [
            'store_id' => $this->store_id,
            'store_name' => $lang === 'ar' ? $this->store_name_ar : $this->store_name_en,
            'product_id' => $this->product_id,
            'product_name' => $lang === 'ar' ? $this->product_name_ar : $this->product_name_en,
            'category_id' => $this->category_id,
            'category_name' => $lang === 'ar' ? $this->category_name_ar : $this->category_name_en,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $lang === 'ar' ? $this->description_ar : $this->description_en,
            'is_favorite' => 1,
            'main_image' => asset($mainUrl),
        ];
    }

}
