<?php

use App\Models\User;

test('login retourne 401 si les identifiants sont invalides', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'mauvais-mot-de-passe',
    ])->assertStatus(401);
});

test('login retourne 403 si l\'utilisateur n\'est pas abonné', function () {
    $user = User::factory()->create(['password' => bcrypt('Test123!')]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'Test123!',
    ])->assertStatus(403);
});

test('login retourne un token si l\'utilisateur est abonné', function () {
    $user = User::factory()->create(['password' => bcrypt('Test123!')]);

    // Créer un abonnement actif directement en DB (pas d'appel Stripe)
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
    ]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'Test123!',
    ])->assertOk()->assertJsonStructure(['token']);
});
