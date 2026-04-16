<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\Localisation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocalisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = User::first()->id;
        $films  = Film::pluck('id');

        $localisations = [
            [
                'film_id'      => $films[0],
                'user_id'      => $userId,
                'name'         => 'Central Park',
                'city'         => 'New York',
                'country'      => 'USA',
                'description'  => "Un grand parc urbain situé au cœur de Manhattan, connu pour ses vastes espaces verts, ses lacs, ses sentiers de promenade et ses événements culturels.",
            ],
            [
                'film_id'      => $films[1] ?? $films[0],
                'user_id'      => $userId,
                'name'         => 'Eiffel Tower',
                'city'         => 'Paris',
                'country'      => 'France',
                'description'  => "Un monument emblématique de Paris, une tour en fer de 324 mètres de haut offrant une vue panoramique sur la ville.",
            ],
        ];

        foreach ($localisations as $localisation) {
            Localisation::query()->firstOrCreate(['name' => $localisation['name']], $localisation);
        }

    }
}
