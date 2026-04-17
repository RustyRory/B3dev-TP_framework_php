<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Localisation;

#[Signature('app:clean-old-localisations')]
#[Description('Supprime les localisations créées depuis plus de 14 jours avec moins de 2 upvotes')]
class CleanOldLocalisations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = Localisation::where('created_at', '<', now()->subDays(14))
            ->where('upvotes_count', '<', 2)
            ->delete();

        $this->info("{$deleted} localisation(s) supprimée(s).");
    }
}
