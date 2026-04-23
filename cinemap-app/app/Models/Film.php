<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Film extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'producer',
        'release_year',
        'time',
        'genres',
        'synopsis',
        'poster_url',
        'trailer_url',
        'actors',
    ];

    public function localisations(): HasMany
    {
        return $this->hasMany(Localisation::class);
    }
}
