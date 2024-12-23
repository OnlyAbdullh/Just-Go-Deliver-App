<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $users = User::factory(5)->create();
            $categories = Category::factory(3)->create();
            $stores = Store::factory(5)->create()->each(function ($store) use ($users) {
                $store->user_id = $users->random()->id;
                $store->save();
            });

            $allProductsData = [];
            $storeProductData = [];
            $productCounter = Product::max('id') + 1;

            foreach ($stores as $store) {
                for ($i = 0; $i < 10; $i++) {
                    $category = $categories->random();

                    $allProductsData[] = [
                        'name_en' => fake()->word(),
                        'name_ar' => fake()->word(),
                        'category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $storeProductData[] = [
                        'store_id' => $store->id,
                        'price' => fake()->randomFloat(2, 10, 500),
                        'quantity' => fake()->numberBetween(1, 100),
                        'description_en' => fake()->sentence(),
                        'description_ar' => fake()->sentence(),
                        'sold_quantity' => fake()->numberBetween(0, 50),
                        'main_image' => 'https://via.placeholder.com/150',
                        'product_id' => $productCounter++,
                    ];
                }
            }

            Product::insert($allProductsData);
            DB::table('store_products')->insert($storeProductData);

        });
    }
}
