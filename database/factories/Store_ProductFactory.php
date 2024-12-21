<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreProduct>
 */
class Store_ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Store_Product::class;

    public function definition()
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'main_image' => 'products/' . $this->faker->image('public/storage/products', 300, 300, null, false),
            'price' => $this->faker->randomFloat(2, 10, 1000), // Random price between 10 and 1000
            'quantity' => $this->faker->numberBetween(1, 100),
            'description_en' => $this->faker->paragraph(),
            'description_ar' => $this->faker->paragraph(),
            'sold_quantity' => $this->faker->numberBetween(0, 50),
        ];
    }
}
