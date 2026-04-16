# Guide de réalisation — CineMap

Ce document est un guide pratique pour réaliser le TP étape par étape.  
Chaque étape doit fonctionner avant de passer à la suivante.

---

## Avant de commencer

Installer le projet en suivant [INSTALL.md](../INSTALL.md), puis vérifier que :

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

> Si l'utilisateur n'est pas connecté, alors qu'il essaye d'accéder au dashboard, Laravel le redirige automatiquement vers `/login`.

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

```php
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

**Objectif :** gérer les films et les localisations de tournage.

### Modèle de données

**Film**

```php
$table->string('name')->unique();
$table->string('producer');
$table->unsignedSmallInteger('release_year');
$table->unsignedSmallInteger('time');          // durée en minutes
$table->string('genres');
$table->text('synopsis');
$table->string('poster_url');
$table->string('trailer_url');
$table->string('actors');
$table->unsignedInteger('upvotes')->default(0);
$table->unsignedInteger('downvotes')->default(0);
```

**Localisation**

```php
$table->foreignId('film_id')->constrained()->cascadeOnDelete();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('name');
$table->string('city');
$table->string('country');
$table->text('description');
$table->string('photo_url')->nullable();
$table->integer('upvotes_count')->default(0);
```

### Ce qu'il faut faire

Créer les fichiers nécessaires via Artisan :

```bash
php artisan make:migration create_films_table
php artisan make:model Film
php artisan make:factory FilmFactory
php artisan make:seeder FilmSeeder
php artisan make:controller FilmController
```

```bash
php artisan make:migration create_localisations_table
php artisan make:model Localisation
php artisan make:factory LocalisationFactory
php artisan make:seeder LocalisationSeeder
php artisan make:controller LocalisationController
php artisan make:controller HomeController
```

#### Models

```php
// app/Models/Film.php
class Film extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'producer', 'release_year', 'time',
        'genres', 'synopsis', 'poster_url', 'trailer_url',
        'actors', 'upvotes', 'downvotes',
    ];

    public function localisations(): HasMany
    {
        return $this->hasMany(Localisation::class);
    }
}
```

```php
// app/Models/Localisation.php
class Localisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'film_id', 'user_id', 'name', 'city', 'country',
        'description', 'photo_url', 'upvotes_count',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

> Attention : `#[Fillable([...])]` n'est **pas** une syntaxe PHP valide. Il faut utiliser la propriété `$fillable`.

> Les `cascadeOnDelete()` sur les FK de `localisations` sont indispensables : sans eux, supprimer un film lève une erreur de contrainte d'intégrité SQLite.

#### Seeders

Le `LocalisationSeeder` doit être appelé **après** `FilmSeeder` car il a besoin des IDs de films existants. Le `user_id` vient de l'utilisateur créé dans `DatabaseSeeder` :

```php
// database/seeders/DatabaseSeeder.php
User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

$this->call([FilmSeeder::class]);
$this->call([LocalisationSeeder::class]);
```

```php
// database/seeders/LocalisationSeeder.php
$userId = User::first()->id;
$films  = Film::pluck('id');

Localisation::query()->firstOrCreate(['name' => 'Central Park'], [
    'film_id' => $films[0], 'user_id' => $userId,
    'city' => 'New York', 'country' => 'USA', ...
]);
```

```bash
php artisan migrate:fresh --seed
```

#### Controllers

**FilmController** : CRUD standard. La validation se fait dans une méthode privée `validatedData()` pour éviter la duplication entre `store` et `update`.

**LocalisationController** :
- `store()` : injecter `user_id` depuis `auth()->id()`, **ne pas** le faire passer par le formulaire.
- `edit()`, `update()`, `destroy()` : vérifier que l'utilisateur est le propriétaire avant d'agir.

```php
// Vérification du propriétaire dans edit/update/destroy
abort_if(auth()->id() !== $localisation->user_id, 403);
```

- `create()` et `edit()` : passer la liste des films pour le `<select>`.

```php
public function create(): View
{
    return view('localisations.create', [
        'films' => Film::query()->orderBy('name')->pluck('name', 'id'),
    ]);
}
```

**HomeController** : charge tous les films avec leurs localisations en eager load.

```php
public function index(): View
{
    $films = Film::query()->with('localisations')->orderBy('name')->get();
    return view('home', ['films' => $films]);
}
```

#### Routes

> **Important :** les routes paramétrées publiques (`/films/{film}`, `/localisations/{localisation}`) doivent être déclarées **après** le groupe `auth`, sinon `/films/create` serait capturé par `{film}` avant la route littérale.

```php
// routes/web.php
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    // Dashboard — gestion des films (futur : admin uniquement)
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{film}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{film}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{film}', [FilmController::class, 'destroy'])->name('films.destroy');

    // Dashboard — liste de toutes les localisations (futur : admin uniquement)
    Route::get('/localisations', [LocalisationController::class, 'index'])->name('localisations.index');

    // Localisations — actions de l'utilisateur connecté (ses propres localisations)
    Route::get('/localisations/create', [LocalisationController::class, 'create'])->name('localisations.create');
    Route::post('/localisations', [LocalisationController::class, 'store'])->name('localisations.store');
    Route::get('/localisations/{localisation}/edit', [LocalisationController::class, 'edit'])->name('localisations.edit');
    Route::put('/localisations/{localisation}', [LocalisationController::class, 'update'])->name('localisations.update');
    Route::delete('/localisations/{localisation}', [LocalisationController::class, 'destroy'])->name('localisations.destroy');
});

// Routes publiques — déclarées APRÈS le groupe auth
Route::get('/films/{film}', [FilmController::class, 'show'])->name('films.show');
Route::get('/localisations/{localisation}', [LocalisationController::class, 'show'])->name('localisations.show');
```

#### Logique de navigation

Le site est divisé en deux espaces distincts :

| Espace | URL | Accès | Rôle |
|---|---|---|---|
| Site public | `/home`, `/films/{film}`, `/localisations/{id}` | Tout le monde | Consultation |
| Espace utilisateur | `/localisations/create`, `/localisations/{id}/edit` | Connecté | Gérer ses localisations |
| Dashboard | `/dashboard`, `/films`, `/localisations` | Connecté (futur : admin) | Administration |

**Règles par action :**

| Action | Visiteur | Utilisateur connecté | Admin (futur) |
|---|---|---|---|
| Voir les films et localisations | Oui | Oui | Oui |
| Ajouter une localisation | Non | Oui | Oui |
| Modifier / supprimer **sa** localisation | Non | Oui | Oui |
| Modifier / supprimer **toutes** les localisations | Non | Non | Oui |
| Ajouter / modifier / supprimer un film | Non | Non | Oui |

**Navigation (`layouts/navigation.blade.php`) :**

- Lien **Accueil** → visible pour tous
- Lien **Dashboard** → visible uniquement si `@auth`
- Dropdown utilisateur (profil, déconnexion) → visible uniquement si `@auth`
- Boutons **Se connecter / S'inscrire** → visibles uniquement si `@guest`

**Boutons conditionnels dans les vues :**

```php
{{-- Bouton "Ajouter une localisation" — visible uniquement si connecté --}}
@auth
    <a href="{{ route('localisations.create', ['film_id' => $film->id]) }}">
        + Ajouter une localisation
    </a>
@endauth

{{-- Boutons modifier/supprimer — visibles uniquement pour le propriétaire --}}
@auth
    @if (auth()->id() === $localisation->user_id)
        <a href="{{ route('localisations.edit', $localisation) }}">Modifier</a>
        {{-- formulaire DELETE --}}
    @endif
@endauth
```

#### Vues

```
resources/views/
├── home.blade.php                     — liste publique des films avec localisations
├── films/
│   ├── index.blade.php                — dashboard : liste paginée + actions CRUD
│   ├── create.blade.php               — dashboard : formulaire de création
│   ├── edit.blade.php                 — dashboard : formulaire pré-rempli
│   └── show.blade.php                 — public : détail film + localisations + bouton ajout
└── localisations/
    ├── index.blade.php                — dashboard : toutes les localisations
    ├── create.blade.php               — formulaire avec select film (pré-sélectionné via ?film_id=)
    ├── edit.blade.php                 — formulaire pré-rempli (propriétaire uniquement)
    └── show.blade.php                 — public : détail localisation + boutons si propriétaire
```

Toutes les vues étendent `<x-app-layout>` et utilisent les composants Breeze (`<x-input-label>`, `<x-text-input>`, `<x-input-error>`, `<x-primary-button>`).

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
| Accès dashboard | Non | Oui |
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
php artisan make:migration create_localisation_votes_table
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
Route::post('/locations/{location}/upvote', [LocalisationController::class, 'upvote'])->middleware('auth');
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
