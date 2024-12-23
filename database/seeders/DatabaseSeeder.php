<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Store_Product;
use Database\Factories\StoreFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
           RolePermessionSeeder::class,
             StoreSeeder::class,
            /*    StoreSeeder::class,
            ProductSeeder::class,
            StoreProductSeeder::class,
            CategorySeeder::class,*/
            // UserSeeder::class
        ]);

        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
