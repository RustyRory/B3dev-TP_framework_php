<?php

use App\Models\Film;

test('GET /api/films retourne 200', function () {
    Film::factory()->create();

    $this->getJson('/api/films')->assertOk();
});

test('GET /api/films retourne les films triés par nom', function () {
    Film::factory()->create(['name' => 'Zorro']);
    Film::factory()->create(['name' => 'Avatar']);

    $response = $this->getJson('/api/films')->assertOk();

    expect($response->json('0.name'))->toBe('Avatar');
});

test('GET /api/films retourne les bons champs', function () {
    Film::factory()->create();

    $this->getJson('/api/films')
        ->assertOk()
        ->assertJsonStructure(['*' => ['id', 'name', 'producer', 'release_year']]);
});
