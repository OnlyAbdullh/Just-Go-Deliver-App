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

    /**
     * @OA\Schema(
     *     schema="StoreResource",
     *     type="object",
     *
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="manager", type="string", example="John Doe"),
     *     @OA\Property(property="name", type="string", example="My Store"),
     *     @OA\Property(property="image_url", type="string", example="http://127.0.0.1:8000/storage/stores/TFmQ589RA4AdMS6thfgp1suFcmv3TsWvEvPNNyUF.jpg"),
     *     @OA\Property(property="location", type="string", example="123 Main St, City, Country", nullable=true),
     *     @OA\Property(property="description", type="string", example="A description of the store")
     * )
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');

        $imagePath = $this->logo;

        $imageUrl = Storage::url($imagePath);
        $logo = asset($imageUrl);
        $name = 'name_'.$lang;
        $description = 'description_'.$lang;
        $location = 'location_'.$lang;

        $storeName = $this->$name;
        $storeDescription = $this->$description;
        $storeLocation = $this->$location;
        $manager = $this->user;
        $managerName = $manager->first_name.' '.$manager->last_name;

        $data = [
            'id' => $this->id,
            'manager' => $managerName,
            'name' => $storeName,
            'image_url' => $logo,
            'location' => $storeLocation,
            'description' => $storeDescription,
        ];

        return $data;
        // return collect([

        // ])->filter(function ($value) {
        //     return ! is_null($value);
        // })->toArray();

    }
}
