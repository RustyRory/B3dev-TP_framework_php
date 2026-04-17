<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(fake()->numberBetween(1, 3), true),
            'producer' => fake()->name(),
            'release_year' => fake()->numberBetween(1900, date('Y')),
            'time' => fake()->numberBetween(30, 300),
            'genres' => implode(', ', fake()->words(fake()->numberBetween(1, 3))),
            'synopsis' => fake()->paragraph(),
            'poster_url' => fake()->url(),
            'trailer_url' => fake()->url(),
            'actors' => implode(', ', fake()->words(fake()->numberBetween(2, 5))),
        ];
    }
}
