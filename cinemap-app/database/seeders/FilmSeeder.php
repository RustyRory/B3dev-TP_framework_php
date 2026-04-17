<?php

namespace Database\Seeders;

use App\Models\Film;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    public function run(): void
    {
        $films = [
            [
                'name' => 'Inception',
                'producer' => 'Christopher Nolan',
                'release_year' => 2010,
                'time' => 148,
                'genres' => 'Action, Sci-Fi, Thriller',
                'synopsis' => "Un voleur qui vole des secrets corporatifs grâce à la technologie de partage de rêves est chargé de la tâche inverse : planter une idée dans l'esprit d'un PDG.",
                'poster_url' => 'https://fr.web.img6.acsta.net/c_310_420/medias/nmedia/18/72/34/14/19476654.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=8hP9D6kZseM',
                'actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page',
            ],
            [
                'name' => 'The Matrix',
                'producer' => 'The Wachowskis',
                'release_year' => 1999,
                'time' => 136,
                'genres' => 'Action, Sci-Fi',
                'synopsis' => 'Un hacker informatique apprend des rebelles mystérieux la nature véritable de sa réalité et son rôle dans la guerre contre ses contrôleurs.',
                'poster_url' => 'https://fr.web.img4.acsta.net/c_310_420/medias/04/34/49/043449_af.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=m8e-FF8MsqU',
                'actors' => 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss',
            ],
        ];

        foreach ($films as $film) {
            Film::query()->firstOrCreate(['name' => $film['name']], $film);
        }

    }
}
