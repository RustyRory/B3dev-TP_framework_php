<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\Localisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Localisation>
 */
class LocalisationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'film_id' => Film::factory(),
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'description' => fake()->paragraph(),
            'upvotes_count' => fake()->numberBetween(0, 100),
        ];
    }
}
