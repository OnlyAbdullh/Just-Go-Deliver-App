<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageFileName = $this->faker->image(
            storage_path('app/public/profiles'), // Path to save the image
            100, // Width
            100, // Height
            null, // Category
            false // Return filename without path
        );
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->boolean(80) ? now() : null,
            'password' => Hash::make('password'),
            'location' => $this->faker->address(),
            'phone_number' => $this->faker->unique()->numerify('##########'),
            'image' => 'profiles/' . $imageFileName,
            'fcm_token' => $this->faker->uuid() ? $this->faker->sha256() : null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
