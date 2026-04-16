<?php

namespace App\Jobs;

use App\Models\Film;
use App\Models\FilmVote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateFilmVotes implements ShouldQueue
{
    use Queueable;

    public function __construct(public Film $film) {}

    public function handle(): void
    {
        $this->film->upvotes_count   = FilmVote::where('film_id', $this->film->id)->where('is_upvote', true)->count();
        $this->film->downvotes_count = FilmVote::where('film_id', $this->film->id)->where('is_upvote', false)->count();
        $this->film->save();
    }
}
