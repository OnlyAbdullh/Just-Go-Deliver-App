<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\Store_Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreProduct>
 */
class StoreProductFactory extends Factory
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
            'price' => $this->faker->randomFloat(2, 10, 500),
            'quantity' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence,
            'sold_quantity' => $this->faker->numberBetween(0, 50),
        ];
    }
}
