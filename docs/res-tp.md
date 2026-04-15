# Guide de réalisation — CineMap

Ce document est un guide pratique pour réaliser le TP étape par étape.  
Chaque étape doit fonctionner avant de passer à la suivante.

---

## Avant de commencer

Installe le projet en suivant [INSTALL.md](../INSTALL.md), puis vérifie que :

- `composer run dev` lance sans erreur
- [http://localhost:8000](http://localhost:8000) répond

---

## Étape 1 — Authentification

**Objectif :** permettre l'inscription, la connexion et la déconnexion.

### Ce qu'il faut faire

1. Installer Laravel Breeze :

```bash
composer require laravel/breeze --dev
php artisan breeze:install
php artisan migrate
npm install
composer run dev
```

2. Vérifier que les pages `/register`, `/login`, `/logout` fonctionnent via le navigateur :

- Aller sur `http://localhost:8000/register` → créer un compte
- Aller sur `http://localhost:8000/login` → se connecter
- Cliquer sur "Log Out" → vérifier la déconnexion


3. Protéger les routes qui nécessitent d'être connecté avec le middleware `auth` :

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nouvelles routes protégées ici
});
```

> Si l'utilisateur n'est pas connecté, Laravel le redirige automatiquement vers `/login`.

4. Créer une page d'accueil `/home` accessible à tous, avec un contenu différent selon l'état de connexion.

#### Route publique

```php
// routes/web.php
Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');
```

#### Vue `resources/views/home.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Accueil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @auth
                        <h3 class="text-lg font-semibold mb-2">Bienvenue, {{ Auth::user()->name }} !</h3>
                        <p class="text-gray-600 dark:text-gray-400">Vous êtes connecté à Cinemap.</p>
                    @else
                        <h3 class="text-lg font-semibold mb-2">Bienvenue sur Cinemap</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Connectez-vous ou créez un compte.</p>
                        <div class="flex gap-4">
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md">Se connecter</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 border border-gray-800 text-gray-800 rounded-md">S'inscrire</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

#### Navigation pour guests (`layouts/navigation.blade.php`)

Entourer les éléments liés à l'utilisateur avec `@auth` / `@endauth` et afficher les liens login/register pour les invités avec `@else`.

#### Redirection vers `/home` après connexion

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
return redirect()->intended(route('home', absolute: false));
```

---

## Étape 2 — Les 2 CRUDs métier

**Objectif :** gérer les films et les emplacements de tournage.

### Modèle de données

**Film**

```php
$table->string('title');
$table->integer('release_year');
$table->text('synopsis');
```

**Location**

```php
$table->foreignId('film_id')->constrained();
$table->foreignId('user_id')->constrained();
$table->string('name');
$table->string('city');
$table->string('country');
$table->text('description');
$table->integer('upvotes_count')->default(0);
```

### Ce qu'il faut faire

Pour chaque modèle, suivre le workflow Laravel :

```bash
php artisan make:migration create_films_table
php artisan make:model Film
php artisan make:controller FilmController
```

```bash
php artisan make:migration create_locations_table
php artisan make:model Location
php artisan make:controller LocationController
```

Pour chaque contrôleur, implémenter :

| Méthode | Route | Description |
|---|---|---|
| `index` | GET `/films` | Liste |
| `create` | GET `/films/create` | Formulaire de création |
| `store` | POST `/films` | Enregistrement |
| `edit` | GET `/films/{id}/edit` | Formulaire de modification |
| `update` | PUT `/films/{id}` | Mise à jour |
| `destroy` | DELETE `/films/{id}` | Suppression |

> Pour `Location`, lors de la création, l'utilisateur doit choisir un film dans une liste déroulante et l'emplacement doit être rattaché à l'utilisateur connecté (`auth()->id()`).

### Checklist

- [ ] Migration `films` lancée
- [ ] Migration `locations` lancée
- [ ] CRUD Film complet (liste, création, édition, suppression)
- [ ] CRUD Location complet
- [ ] Création d'un emplacement rattaché à un film et à l'utilisateur connecté

---

## Étape 3 — Middleware administrateur

**Objectif :** distinguer les droits d'un admin de ceux d'un utilisateur classique.

### Ce qu'il faut faire

1. Ajouter le champ `is_admin` à la table `users` :

```bash
php artisan make:migration add_is_admin_to_users_table
```

```php
$table->boolean('is_admin')->default(false);
```

2. Créer le middleware :

```bash
php artisan make:middleware AdminMiddleware
```

Dans `handle()`, vérifier `auth()->user()->is_admin`.

3. Enregistrer le middleware dans `bootstrap/app.php` (Laravel 11) ou `Kernel.php` (Laravel 10).

4. Appliquer les règles :

| Action | Utilisateur classique | Admin |
|---|---|---|
| Créer un film | Non | Oui |
| Modifier / supprimer un film | Non | Oui |
| Créer un emplacement | Oui | Oui |
| Modifier / supprimer **ses** emplacements | Oui | Oui |
| Modifier / supprimer **tous** les emplacements | Non | Oui |

### Checklist

- [ ] Champ `is_admin` ajouté
- [ ] Middleware `admin` créé et enregistré
- [ ] Routes admin protégées
- [ ] Un utilisateur classique ne peut modifier que ses propres emplacements

---

## Étape 4 — Upvotes + Queue & Jobs

**Objectif :** ajouter un bouton upvote sur les emplacements, traité en arrière-plan.

### Modèle de données

```bash
php artisan make:migration create_location_votes_table
```

```php
$table->foreignId('user_id')->constrained();
$table->foreignId('location_id')->constrained();
$table->timestamp('created_at');
$table->unique(['user_id', 'location_id']); // 1 vote par utilisateur
```

### Ce qu'il faut faire

1. Créer la table `location_votes`.
2. Ajouter une route et une action de vote (pas de CRUD complet — juste un bouton POST) :

```php
// routes/web.php
Route::post('/locations/{location}/upvote', [LocationController::class, 'upvote'])->middleware('auth');
```

3. Dans l'action `upvote`, enregistrer le vote et dispatcher un job :

```bash
php artisan make:job RecalculateUpvotes
```

```php
// Dans le job : recalculer upvotes_count sur l'emplacement
$location->upvotes_count = LocationVote::where('location_id', $location->id)->count();
$location->save();
```

4. Configurer la queue dans `.env` :

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
```

5. Vérifier que le job passe bien par le worker (`php artisan queue:listen`).

### Checklist

- [ ] Table `location_votes` créée avec contrainte d'unicité
- [ ] Bouton upvote visible sur la page d'un emplacement
- [ ] Un utilisateur ne peut voter qu'une seule fois
- [ ] Job `RecalculateUpvotes` dispatché après le vote
- [ ] `upvotes_count` mis à jour après traitement par le worker

---

## Étape 5 — Commande Artisan + tâche planifiée

**Objectif :** nettoyer automatiquement les emplacements inactifs chaque jour.

### Règle métier

Supprimer les emplacements créés **depuis plus de 14 jours** et ayant **moins de 2 upvotes**.

### Ce qu'il faut faire

1. Créer la commande :

```bash
php artisan make:command CleanOldLocations
```

Dans `handle()` :

```php
Location::where('created_at', '<', now()->subDays(14))
    ->where('upvotes_count', '<', 2)
    ->delete();
```

2. Enregistrer dans le scheduler (`routes/console.php` ou `app/Console/Kernel.php`) :

```php
Schedule::command('app:clean-old-locations')->daily();
```

3. Tester manuellement :

```bash
php artisan app:clean-old-locations
```

### Checklist

- [ ] Commande créée avec la bonne règle métier (14 jours / < 2 upvotes)
- [ ] Commande enregistrée dans le scheduler (quotidienne)
- [ ] Test manuel fonctionnel

---

## Étape 6 — Laravel Pint

**Objectif :** formater tout le code avant le rendu.

### Ce qu'il faut faire

```bash
./vendor/bin/pint
```

> Lancez cette commande **avant chaque commit** et obligatoirement avant le rendu final.

### Checklist

- [ ] `./vendor/bin/pint` s'exécute sans erreur
- [ ] Commande mentionnée dans le README

---

## Étape 7 — Connexion OAuth

**Objectif :** ajouter un bouton "Se connecter avec GitHub" (ou autre) sur la page de login.

### Ce qu'il faut faire

1. Installer Socialite :

```bash
composer require laravel/socialite
```

2. Configurer le fournisseur dans `config/services.php` :

```php
'github' => [
    'client_id'     => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect'      => env('GITHUB_REDIRECT_URI'),
],
```

3. Ajouter les routes OAuth :

```php
Route::get('/auth/github', [AuthController::class, 'redirectToGithub']);
Route::get('/auth/github/callback', [AuthController::class, 'handleGithubCallback']);
```

4. Dans le callback, créer ou connecter l'utilisateur via `User::firstOrCreate()`.

5. Ajouter le bouton sur la vue login.

### Checklist

- [ ] Package Socialite installé
- [ ] Variables `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET`, `GITHUB_REDIRECT_URI` dans `.env`
- [ ] Bouton OAuth visible sur la page de login
- [ ] Connexion OAuth crée ou retrouve l'utilisateur en base

---

## Étape 8 — Abonnement Stripe + API JSON protégée par JWT

**Objectif :** exposer une API JSON réservée aux utilisateurs abonnés et authentifiés par JWT.

### Route attendue

```
GET /api/films/{film}/locations
Authorization: Bearer <token_jwt>
```

### Ce qu'il faut faire

#### Stripe

1. Installer Cashier :

```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

2. Ajouter le trait `Billable` au modèle `User`.

3. Créer une page de souscription simple avec un formulaire Stripe.

4. Tester avec la carte `4242 4242 4242 4242`.

#### JWT

1. Installer `php-open-source-saver/jwt-auth` (compatible Laravel 11) :

```bash
composer require php-open-source-saver/jwt-auth
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

2. Ajouter dans `.env` :

```env
JWT_SECRET=<valeur générée>
```

3. Créer les routes d'auth API :

```php
// routes/api.php
Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::middleware(['auth:api', 'subscribed'])->group(function () {
    Route::get('/films/{film}/locations', [FilmApiController::class, 'locations']);
});
```

4. La réponse JSON doit contenir : infos du film + liste des emplacements + `upvotes_count`.

### Checklist

- [ ] Stripe fonctionnel en mode test (carte `4242 4242 4242 4242`)
- [ ] Page de souscription accessible
- [ ] `JWT_SECRET` généré et configuré
- [ ] Route `POST /api/auth/login` retourne un token JWT
- [ ] Route `GET /api/films/{film}/locations` accessible uniquement avec JWT valide + abonnement actif
- [ ] Réponse JSON contient les infos du film et ses emplacements

---

## Étape 9 — Serveur MCP

**Objectif :** exposer deux outils en lecture seule pour permettre à une IA d'interroger l'application.

### Outils attendus

| Outil | Description |
|---|---|
| `list_films` | Retourne la liste de tous les films |
| `get_locations_for_film` | Retourne les emplacements d'un film donné |

### Ce qu'il faut faire

1. Choisir une implémentation (package PHP MCP, script Node.js, pont vers l'API JSON existante…).
2. Implémenter les deux outils en lecture seule.
3. Documenter le lancement du serveur MCP dans le README.
4. Tester avec un client MCP compatible (ex. Claude Desktop, un client MCP CLI…).

> Conseil : réutiliser la route `/api/films/{film}/locations` déjà créée à l'étape 8 pour `get_locations_for_film`.

### Checklist

- [ ] Serveur MCP démarre sans erreur
- [ ] `list_films` retourne la liste des films
- [ ] `get_locations_for_film` retourne les emplacements d'un film
- [ ] Lancement documenté dans le README

---

## Avant le rendu — checklist finale

- [ ] `./vendor/bin/pint` lancé sur tout le code
- [ ] Toutes les migrations sont propres (`php artisan migrate:fresh` sans erreur)
- [ ] Le worker de queue fonctionne
- [ ] La commande planifiée est testable manuellement
- [ ] L'auth OAuth fonctionne
- [ ] Stripe fonctionne en mode test
- [ ] L'API répond correctement avec un JWT valide + abonnement
- [ ] Le serveur MCP fonctionne
- [ ] Le README explique clairement comment lancer chaque partie
