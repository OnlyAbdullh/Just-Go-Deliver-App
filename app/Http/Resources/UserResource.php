<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $mainUrl = Storage::url($this->image);

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'location' => $this->location,
            'phone_number' => $this->phone_number,
            'image_url' => asset($mainUrl),
            'role' => $this->roles->pluck('name')->first(),
        ];
    }
}
