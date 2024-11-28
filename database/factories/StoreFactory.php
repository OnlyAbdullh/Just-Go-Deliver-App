<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
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
    public function definition(): array
    {
        return  [
            'user_id' => User::factory(),
            'name' => $this->faker->company,
            'logo' => 'logos/' . $this->faker->image('public/storage/logos', 400, 400, null, false),
            'description' => $this->faker->sentence(10),
        ];
    }
}
