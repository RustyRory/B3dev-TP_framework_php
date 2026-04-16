<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard — gestion des films (auth, plus tard admin)
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{film}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{film}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{film}', [FilmController::class, 'destroy'])->name('films.destroy');

    // Dashboard — liste de toutes les localisations (auth, plus tard admin)
    Route::get('/localisations', [LocalisationController::class, 'index'])->name('localisations.index');

    // Localisations — actions utilisateur (ses propres localisations)
    Route::get('/localisations/create', [LocalisationController::class, 'create'])->name('localisations.create');
    Route::post('/localisations', [LocalisationController::class, 'store'])->name('localisations.store');
    Route::get('/localisations/{localisation}/edit', [LocalisationController::class, 'edit'])->name('localisations.edit');
    Route::put('/localisations/{localisation}', [LocalisationController::class, 'update'])->name('localisations.update');
    Route::delete('/localisations/{localisation}', [LocalisationController::class, 'destroy'])->name('localisations.destroy');
});

// Routes publiques paramétrées — définies APRÈS le groupe auth
// pour que /films/create et /localisations/create soient capturés en premier
Route::get('/films/{film}', [FilmController::class, 'show'])->name('films.show');
Route::get('/localisations/{localisation}', [LocalisationController::class, 'show'])->name('localisations.show');

require __DIR__.'/auth.php';
