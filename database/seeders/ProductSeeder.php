<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Category::factory()->count(30)->create();

        foreach (range(1, 30) as $index) {
            Product::create([
                'name' => 'Product ' . $index,
                'category_id' => Category::inRandomOrder()->first()->id,
            ]);
        }
    }

}
