<?php

use App\Models\Film;
use App\Models\User;

test('GET /api/films/{film}/localisations retourne 401 sans token', function () {
    $film = Film::factory()->create();

    $this->getJson("/api/films/{$film->id}/localisations")
        ->assertStatus(401);
});

test('GET /api/films/{film}/localisations retourne 200 avec un utilisateur authentifié', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();

    $this->actingAs($user, 'api')
        ->getJson("/api/films/{$film->id}/localisations")
        ->assertOk()
        ->assertJsonStructure(['film', 'localisations']);
});
