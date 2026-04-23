<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\FilmApiController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::get('/films', [FilmApiController::class, 'index']);
Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/films/{film}/locations', [FilmApiController::class, 'locations']);
});
