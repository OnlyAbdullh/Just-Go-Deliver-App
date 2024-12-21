<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="CategoryResource",
     *     type="object",
     *     title="Category Resource",
     *     description="Represents a category resource with translated name based on the selected language.",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="The unique identifier of the category",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="The translated name of the category based on the Accept-Language header",
     *         example="Category Name in Selected Language"
     *     )
     * )
     */
    public function toArray(Request $request): array
    {
        $lang = app()->getLocale();
        $name = 'name_' . $lang;
        return [
            'id' => $this->id,
            'name' => $this->$name,
        ];
    }
}
