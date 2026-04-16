<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\FilmVote;
use App\Models\LocalisationVote;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $films = Film::query()
            ->with('localisations')
            ->orderBy('name')
            ->get();

        $filmVotes          = collect();
        $localisationVotes  = collect();

        if (auth()->check()) {
            $filmVotes = FilmVote::where('user_id', auth()->id())
                ->whereIn('film_id', $films->pluck('id'))
                ->get()
                ->keyBy('film_id');

            $localisationIds = $films->flatMap(fn ($f) => $f->localisations->pluck('id'));

            $localisationVotes = LocalisationVote::where('user_id', auth()->id())
                ->whereIn('localisation_id', $localisationIds)
                ->get()
                ->keyBy('localisation_id');
        }

        return view('home', compact('films', 'filmVotes', 'localisationVotes'));
    }
}
