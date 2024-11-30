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

        $manager = $this->user;
        return [
            'id' => $this->id,
            'manager' => $manager->first_name . ' ' . $manager->last_name,
            'name' => $this->name,
            'image_url' => asset($imageUrl),
            'location' => $this->when(!empty($this->location), $this->location),
            'description' => $this->description
        ];
    }
}
