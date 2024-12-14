<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create some sample categories
        Category::create(['name' => 'Electronics']);
        Category::create(['name' => 'Furniture']);
        Category::create(['name' => 'Stationery']);
    }
}
