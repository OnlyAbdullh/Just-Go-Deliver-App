<?php

namespace Database\Seeders;
use Faker\Factory as Faker;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create a Faker instance
        $faker = Faker::create();

        // Create categories using the factory, if they don't exist already
        Category::factory()->count(30)->create();  // Create 5 categories for example

        // Create products with valid category_ids
        $products = Product::factory()->count(30)->create();

        // Create stores
        $stores = Store::factory()->count(30)->create();

        // Assign products to stores with store_product table data
        foreach ($stores as $store) {
            foreach ($products as $product) {
                \App\Models\Store_Product::create([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'price' => $faker->randomFloat(2, 5, 500), // Random price between 5 and 500
                    'quantity' => $faker->numberBetween(1, 100), // Random quantity between 1 and 100
                    'description' => 'Product description for ' . $product->name,
                    'sold_quantity' => $faker->numberBetween(1, 50), // Random sold quantity between 1 and 50
                    'main_image' => 'main_image.png', // You can set a placeholder value
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }


}
