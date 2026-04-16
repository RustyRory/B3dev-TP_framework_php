<?php

namespace App\Jobs;

use App\Models\Localisation;
use App\Models\LocalisationVote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateLocalisationVotes implements ShouldQueue
{
    use Queueable;

    public function __construct(public Localisation $localisation) {}

    public function handle(): void
    {
        $this->localisation->upvotes_count = LocalisationVote::where('localisation_id', $this->localisation->id)->count();
        $this->localisation->save();
    }
}
