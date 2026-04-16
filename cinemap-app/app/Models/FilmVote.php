<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmVote extends Model
{
    protected $fillable = [
        'user_id',
        'film_id',
        'is_upvote',
    ];

    protected $casts = [
        'is_upvote' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}
