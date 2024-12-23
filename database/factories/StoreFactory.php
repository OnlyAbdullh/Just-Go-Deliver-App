<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Store::class;

    public function definition()
    {
        $storesImages = Storage::disk('public')->files('stores');

        $randomImage = $storesImages[array_rand($storesImages)];

        $imageUrl = asset($randomImage);
        $imageUrl = str_replace("http://localhost", "", $imageUrl);
        return [
            'user_id' => User::factory(),
            'name_ar' => $this->faker->unique()->words(2, true),
            'name_en' => $this->faker->unique()->words(2, true),
            'logo' => $imageUrl,
            'location_ar' => $this->faker->address(),
            'location_en' => $this->faker->address(),
            'description_ar' => $this->faker->sentence(),
            'description_en' => $this->faker->sentence(),
        ];
    }
}
