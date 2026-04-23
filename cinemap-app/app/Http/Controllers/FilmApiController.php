<?php

namespace App\Http\Controllers;

use App\Models\Film;

class FilmApiController extends Controller
{
    public function locations(Film $film)
    {
        return response()->json([
            'film' => $film->only(['id', 'name', 'producer', 'release_year', 'synopsis']),
            'localisations' => $film->localisations()->get([
                'id', 'name', 'city', 'country', 'description', 'upvotes_count',
            ]),
        ]);
    }

    public function index()
    {
        return response()->json(
            Film::query()->orderBy('name')->get(['id', 'name', 'producer', 'release_year'])
        );
    }
}
