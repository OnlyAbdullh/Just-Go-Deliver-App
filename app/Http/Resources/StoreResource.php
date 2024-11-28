<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imagePath = $this->logo;

        $imageUrl = Storage::url($imagePath);
        return [
            'manager' => $this->user->first_name . ' ' . $this->user->last_name,
            'name' => $this->name,
            'image_url' => asset($imageUrl),
            'location' => $this->when(!empty($this->location), $this->location),
            'description' => $this->description
        ];
    }
}
