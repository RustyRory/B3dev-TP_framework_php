<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Film;
use App\Models\Localisation;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/about', fn () => view('about'))->name('about');

Route::get('/dashboard', function () {
    return view('dashboard', [
        'totalFilms' => Film::count(),
        'totalLocalisations' => Localisation::count(),
        'totalFilmUpvotes' => Film::sum('upvotes_count'),
        'totalFilmDownvotes' => Film::sum('downvotes_count'),
        'totalLocVotes' => Localisation::sum('upvotes_count'),
        'topFilms' => Film::orderByDesc('upvotes_count')->take(5)->get(['id', 'name', 'upvotes_count', 'downvotes_count']),
        'topLocalisations' => Localisation::with('film')->orderByDesc('upvotes_count')->take(5)->get(['id', 'film_id', 'name', 'upvotes_count']),
    ]);
})->middleware(['auth', 'admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Localisations — actions utilisateur (ses propres localisations)
    Route::get('/localisations/create', [LocalisationController::class, 'create'])->name('localisations.create');
    Route::post('/localisations', [LocalisationController::class, 'store'])->name('localisations.store');
    Route::get('/localisations/{localisation}/edit', [LocalisationController::class, 'edit'])->name('localisations.edit');
    Route::put('/localisations/{localisation}', [LocalisationController::class, 'update'])->name('localisations.update');
    Route::delete('/localisations/{localisation}', [LocalisationController::class, 'destroy'])->name('localisations.destroy');

    // Votes — actions utilisateur (sur toutes les localisations/films)
    Route::post('/localisations/{localisation}/vote', [LocalisationController::class, 'vote'])->name('localisations.vote');
    Route::post('/films/{film}/vote', [FilmController::class, 'vote'])->name('films.vote');

    // Abonnement
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
});

// Dashboard — réservé aux admins
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{film}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{film}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{film}', [FilmController::class, 'destroy'])->name('films.destroy');

    Route::get('/localisations', [LocalisationController::class, 'index'])->name('localisations.index');
});

// Routes publiques paramétrées — définies APRÈS le groupe auth
// pour que /films/create et /localisations/create soient capturés en premier
Route::get('/films/{film}', [FilmController::class, 'show'])->name('films.show');
Route::get('/localisations/{localisation}', [LocalisationController::class, 'show'])->name('localisations.show');

// Routes d'authentification via Discord
Route::get('/auth/discord', [SocialiteController::class, 'redirectToDiscord']);
Route::get('/auth/discord/callback', [SocialiteController::class, 'handleDiscordCallback']);

require __DIR__.'/auth.php';
