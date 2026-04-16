<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalisationVote extends Model
{
    protected $fillable = ['user_id', 'localisation_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function localisation(): BelongsTo
    {
        return $this->belongsTo(Localisation::class);
    }
}

