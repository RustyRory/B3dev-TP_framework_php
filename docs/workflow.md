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

    // Nouvelles routes ici
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

#### Ajouter le champ `is_admin` à la table `users` :

```bash
php artisan make:migration add_is_admin_to_users_table
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false)->after('password');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('is_admin');
    });
}
```

```bash
php artisan migrate
```

#### Créer le middleware :

1. Générer le middleware

```bash
php artisan make:middleware AdminMiddleware
```
Cela crée `app/Http/Middleware/AdminMiddleware.php`.

2. Écrire la logique dans handle()

Le fichier généré contient une méthode handle(). Il faut y vérifier que l'utilisateur est connecté et admin, sinon rediriger ou retourner une 403 :

```php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->check() || ! auth()->user()->is_admin) {
        abort(403);
    }

    return $next($request);
}
```

3. Enregistrer le middleware dans bootstrap/app.php 

Les middlewares s'enregistrent dans `bootstrap/app.php` via `withMiddleware()` :

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

alias permet de lui donner un nom court ('admin') pour l'utiliser dans les routes.

4. Utiliser le middleware sur les routes

Une fois enregistré, protéger les routes du dashboard :

```php
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
```

auth vérifie que l'utilisateur est connecté, admin vérifie qu'il est admin. Les deux sont nécessaires — admin seul planterait si personne n'est connecté (auth()->user() serait null).

#### Seed Admin

Vérifier que ça fonctionne
Pour tester sans implémenter de page d'admin, passer temporairement is_admin = true à l'utilisateur du seeder et lui donner un mot de passe généré manuellement:

```php
User::factory()->create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => 'Admin123!',
    'is_admin' => true,
]);
```

#### Appliquer les règles :

| Action | Utilisateur classique | Admin |
|---|---|---|
| Accès dashboard | Non | Oui |
| Créer un film | Non | Oui |
| Modifier / supprimer un film | Non | Oui |
| Créer un emplacement | Oui | Oui |
| Modifier / supprimer **ses** emplacements | Oui | Oui |
| Modifier / supprimer **tous** les emplacements | Non | Oui |

1. routes/web.php 

Déjà fait

2. LocalisationController.php — modifier edit, update, destroy

Actuellement la règle est : propriétaire uniquement. Il faut la changer en : propriétaire OU admin.

```php
// Actuellement :
abort_if(auth()->id() !== $localisation->user_id, 403);

// À remplacer par :
abort_if(
    auth()->id() !== $localisation->user_id && ! auth()->user()->is_admin,
    403
);
```

À appliquer dans les 3 méthodes : edit(), update(), destroy().

3. Vues — masquer les boutons dashboard aux non-admins

`films/show.blade.php` et `localisations/index.blade.php`
Les boutons "Modifier"/"Supprimer" dans la vue `films/show` sur les localisations doivent aussi tenir compte de l'admin :

```php
// Actuellement :
@if (auth()->id() === $localisation->user_id)

// À remplacer par :
@if (auth()->id() === $localisation->user_id || auth()->user()->is_admin)
navigation.blade.php
Le lien "Dashboard" dans la nav ne devrait être visible que pour les admins :


// Actuellement :
@auth
    <x-nav-link :href="route('dashboard')">Dashboard</x-nav-link>
@endauth

// À remplacer par :
@auth
    @if (auth()->user()->is_admin)
        <x-nav-link :href="route('dashboard')">Dashboard</x-nav-link>
    @endif
@endauth
```

---

## Étape 4 — Votes + Queue & Jobs

**Objectif :** ajouter un système de vote sur les localisations (upvote uniquement) et sur les films (upvote + downvote), traité en arrière-plan via une queue.

### 1. Nettoyage — supprimer les anciens compteurs

Avant de créer le nouveau modèle de données, supprimer les références aux anciens champs dans les fichiers suivants.

#### Migrations existantes

`create_films_table.php` — supprimer :
```php
$table->unsignedInteger('upvotes')->default(0);
$table->unsignedInteger('downvotes')->default(0);
```

`create_localisations_table.php` — supprimer :
```php
$table->integer('upvotes_count')->default(0);
```

#### Modèles

`Film.php` — retirer du `$fillable` :
```php
'upvotes',
'downvotes',
```

`Localisation.php` — retirer du `$fillable` :
```php
'upvotes_count',
```

#### Factories

`FilmFactory.php` — supprimer :
```php
'upvotes'   => fake()->numberBetween(0, 500),
'downvotes' => fake()->numberBetween(0, 100),
```

`LocalisationFactory.php` — supprimer :
```php
'upvotes_count' => fake()->numberBetween(0, 100),
```

#### Vues

`localisations/index.blade.php` — supprimer la `<th>` Votes et la `<td>` correspondante :
```html
<td ...>{{ $localisation->upvotes_count }}</td>
```

`localisations/show.blade.php` — supprimer le bloc `<dt>`/`<dd>` :
```html
<dd ...>{{ $localisation->upvotes_count }}</dd>
```

### 2. Modèle de données

Remettre les compteurs dénormalisés dans les migrations existantes :

- `create_localisations_table.php` → `$table->unsignedInteger('upvotes_count')->default(0);`
- `create_films_table.php` → `$table->unsignedInteger('upvotes_count')->default(0);`
- `create_films_table.php` → `$table->unsignedInteger('downvotes_count')->default(0);`

Créer les deux tables de votes :

```bash
php artisan make:migration create_localisation_votes_table
php artisan make:migration create_film_votes_table
```

`localisation_votes` — upvote uniquement :

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('localisation_id')->constrained()->cascadeOnDelete();
$table->unique(['user_id', 'localisation_id']); // 1 vote par utilisateur
$table->timestamps();
```

`film_votes` — upvote **et** downvote :

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('film_id')->constrained()->cascadeOnDelete();
$table->boolean('is_upvote'); // true = upvote, false = downvote
$table->unique(['user_id', 'film_id']); // 1 vote par utilisateur
$table->timestamps();
```

> **Attention :** ne pas ajouter `$table->timestamp('created_at')` manuellement — `timestamps()` génère déjà `created_at` et `updated_at`.

> **Attention :** si les deux migrations sont générées à la même seconde, renommer l'une d'elles avec un timestamp décalé d'une seconde pour éviter les conflits d'ordre d'exécution.

Puis recréer la base :

```bash
php artisan migrate:fresh --seed
```

### 3. Modèles

Créer les deux modèles :

```bash
php artisan make:model LocalisationVote
php artisan make:model FilmVote
```

`LocalisationVote` :

```php
protected $fillable = ['user_id', 'localisation_id'];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function localisation(): BelongsTo
{
    return $this->belongsTo(Localisation::class);
}
```

`FilmVote` :

```php
protected $fillable = ['user_id', 'film_id', 'is_upvote'];

protected $casts = ['is_upvote' => 'boolean'];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function film(): BelongsTo
{
    return $this->belongsTo(Film::class);
}
```

### 4. Routes

Dans le groupe `middleware('auth')` existant de `routes/web.php` :

```php
Route::post('/localisations/{localisation}/vote', [LocalisationController::class, 'vote'])
    ->name('localisations.vote');
Route::post('/films/{film}/vote', [FilmController::class, 'vote'])
    ->name('films.vote');
```

### 5. Jobs de recalcul

```bash
php artisan make:job RecalculateLocalisationVotes
php artisan make:job RecalculateFilmVotes
```

`RecalculateLocalisationVotes` — le modèle est injecté via le constructeur :

```php
public function __construct(public Localisation $localisation) {}

public function handle(): void
{
    $this->localisation->upvotes_count = LocalisationVote::where('localisation_id', $this->localisation->id)->count();
    $this->localisation->save();
}
```

`RecalculateFilmVotes` :

```php
public function __construct(public Film $film) {}

public function handle(): void
{
    $this->film->upvotes_count   = FilmVote::where('film_id', $this->film->id)->where('is_upvote', true)->count();
    $this->film->downvotes_count = FilmVote::where('film_id', $this->film->id)->where('is_upvote', false)->count();
    $this->film->save();
}
```

> **Attention :** ne pas oublier les imports (`use App\Models\...`) en haut de chaque fichier de job.

### 6. Actions dans les controllers

`LocalisationController@vote` — toggle (re-cliquer = annuler le vote) :

```php
public function vote(Localisation $localisation): RedirectResponse
{
    $existing = LocalisationVote::where([
        'user_id'          => auth()->id(),
        'localisation_id'  => $localisation->id,
    ])->first();

    $existing
        ? $existing->delete()
        : LocalisationVote::create([
            'user_id'         => auth()->id(),
            'localisation_id' => $localisation->id,
        ]);

    RecalculateLocalisationVotes::dispatch($localisation);

    return back();
}
```

`FilmController@vote` — toggle ou changement de sens :

```php
public function vote(Request $request, Film $film): RedirectResponse
{
    $existing = FilmVote::where([
        'user_id' => auth()->id(),
        'film_id' => $film->id,
    ])->first();

    if ($existing) {
        $existing->is_upvote === $request->boolean('is_upvote')
            ? $existing->delete()
            : $existing->update(['is_upvote' => $request->boolean('is_upvote')]);
    } else {
        FilmVote::create([
            'user_id'   => auth()->id(),
            'film_id'   => $film->id,
            'is_upvote' => $request->boolean('is_upvote'),
        ]);
    }

    RecalculateFilmVotes::dispatch($film);

    return back();
}
```

### 7. Configuration de la queue

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
php artisan queue:listen
```

### 8. Boutons de vote dans les vues

Les controllers passent le vote de l'utilisateur connecté à la vue pour colorier le bouton actif.

`LocalisationController@show` — passer `$userHasVoted` :

```php
'userHasVoted' => auth()->check()
    ? LocalisationVote::where(['user_id' => auth()->id(), 'localisation_id' => $localisation->id])->exists()
    : false,
```

`FilmController@show` — passer `$userVote` :

```php
'userVote' => auth()->check()
    ? FilmVote::where(['user_id' => auth()->id(), 'film_id' => $film->id])->first()
    : null,
```

**`localisations/show.blade.php`** — bouton upvote (toggle) :

- Connecté : bouton vert si déjà voté, gris sinon. Cliquer à nouveau annule le vote.
- Non connecté : compteur affiché en lecture seule + lien vers login.

```php
@auth
    <form action="{{ route('localisations.vote', $localisation) }}" method="POST">
        @csrf
        <button type="submit"
                class="{{ $userHasVoted ? 'bg-green-600 text-white' : 'bg-gray-100 ...' }} ...">
            +{{ $localisation->upvotes_count }}
        </button>
    </form>
@else
    <span>+{{ $localisation->upvotes_count }}</span>
    <a href="{{ route('login') }}">Connectez-vous</a> pour voter.
@endauth
```

**`films/show.blade.php`** — boutons upvote + downvote :

- Connecté : bouton vert si upvote actif, rouge si downvote actif. Cliquer sur le même bouton annule le vote, cliquer sur l'autre change de sens.
- Non connecté : compteurs en lecture seule + lien vers login.
- Chaque bouton envoie un champ caché `is_upvote` (valeur `1` ou `0`) en POST sur `films.vote`.

```php
@auth
    {{-- Upvote --}}
    <form action="{{ route('films.vote', $film) }}" method="POST">
        @csrf
        <input type="hidden" name="is_upvote" value="1">
        <button class="{{ $userVote?->is_upvote === true ? 'bg-green-600 text-white' : '...' }} ...">
            +{{ $film->upvotes_count }}
        </button>
    </form>
    {{-- Downvote --}}
    <form action="{{ route('films.vote', $film) }}" method="POST">
        @csrf
        <input type="hidden" name="is_upvote" value="0">
        <button class="{{ $userVote?->is_upvote === false ? 'bg-red-600 text-white' : '...' }} ...">
            -{{ $film->downvotes_count }}
        </button>
    </form>
@else
    <span>+{{ $film->upvotes_count }}</span>
    <span>-{{ $film->downvotes_count }}</span>
    <a href="{{ route('login') }}">Connectez-vous</a> pour voter.
@endauth
```

**`home.blade.php`** — votes film affichés à côté du titre (boutons `+N` / `-N` fusionnés en pill), upvote localisation en bout de card.

**Données à passer depuis les controllers :**

`LocalisationController@show` :
```php
'userHasVoted' => auth()->check()
    ? LocalisationVote::where(['user_id' => auth()->id(), 'localisation_id' => $localisation->id])->exists()
    : false,
```

`FilmController@show` — passer aussi les votes de localisations pour la liste :
```php
'userVote'          => auth()->check() ? FilmVote::where([...])->first() : null,
'localisationVotes' => auth()->check()
    ? LocalisationVote::where('user_id', auth()->id())
        ->whereIn('localisation_id', $film->localisations->pluck('id'))
        ->get()->keyBy('localisation_id')
    : collect(),
```

`HomeController@index` — une requête par type de vote, sans N+1 :
```php
$filmVotes = FilmVote::where('user_id', auth()->id())
    ->whereIn('film_id', $films->pluck('id'))->get()->keyBy('film_id');

$localisationVotes = LocalisationVote::where('user_id', auth()->id())
    ->whereIn('localisation_id', $localisationIds)->get()->keyBy('localisation_id');
```

### 9. Dashboard et tables admin

La route `/dashboard` passe des statistiques agrégées à la vue :

```php
Route::get('/dashboard', function () {
    return view('dashboard', [
        'totalFilms'         => Film::count(),
        'totalLocalisations' => Localisation::count(),
        'totalFilmUpvotes'   => Film::sum('upvotes_count'),
        'totalFilmDownvotes' => Film::sum('downvotes_count'),
        'totalLocVotes'      => Localisation::sum('upvotes_count'),
        'topFilms'           => Film::orderByDesc('upvotes_count')->take(5)->get([...]),
        'topLocalisations'   => Localisation::with('film')->orderByDesc('upvotes_count')->take(5)->get([...]),
    ]);
})->middleware(['auth', 'admin'])->name('dashboard');
```

`dashboard.blade.php` affiche :
- 5 compteurs globaux (films, localisations, upvotes films, downvotes films, upvotes localisations)
- Top 5 films : upvotes, downvotes, score net (`upvotes_count - downvotes_count`)
- Top 5 localisations : upvotes + film associé

`films/index.blade.php` — colonnes **Upvotes** et **Downvotes** ajoutées au tableau admin.

`localisations/index.blade.php` — colonne **Upvotes** ajoutée au tableau admin.

### 10. Vérification

1. Lancer le worker dans un terminal dédié : `php artisan queue:listen`
2. Se connecter et cliquer sur un bouton de vote
3. Le terminal doit afficher : `App\Jobs\RecalculateLocalisationVotes` (ou `RecalculateFilmVotes`) avec statut `DONE`
4. Rafraîchir la page → le compteur est mis à jour et le bouton est colorié
5. Vérifier le dashboard admin → les stats et tops reflètent les votes

---

## Étape 5 — Commande Artisan + tâche planifiée

**Objectif :** nettoyer automatiquement les emplacements inactifs chaque jour.

### Règle métier

Supprimer les emplacements créés **depuis plus de 14 jours** et ayant **moins de 2 upvotes**.

### Ce qu'il faut faire

1. Créer la commande :

```bash
php artisan make:command CleanOldLocalisations
```

Dans `handle()` :

```php
Localisation::where('created_at', '<', now()->subDays(14))
    ->where('upvotes_count', '<', 2)
    ->delete();
```

2. Enregistrer dans le scheduler (`routes/console.php`) :

```php
Schedule::command('app:clean-old-localisations')->daily();
```

3. Tester manuellement :

```bash
php artisan app:clean-old-localisations
```

---

## Étape 6 — Laravel Pint

**Objectif :** formater tout le code avant le rendu.

### Ce qu'il faut faire

Depuis le dossier cinemap-app :

```bash
cd cinemap-app
./vendor/bin/pint
```

#### Vérifier sans modifier (dry-run)

Si l'on veut voir ce qui serait changé sans toucher aux fichiers :

```bash
./vendor/bin/pint --test
```
> Retourne un code d'erreur si des fichiers ne sont pas conformes (utile en CI).

### git hook — pre-commit

> Lancez cette commande **avant chaque commit** et obligatoirement avant le rendu final.

#### Mise en place

Crée le fichier .git/hooks/pre-commit :

```bash
#!/bin/sh

cd cinemap-app && ./vendor/bin/pint

git add -u
```

#### Ce qui se passe à chaque git commit

1. Pint formate tous les fichiers PHP
2. git add -u re-stage les fichiers modifiés par Pint
3. Le commit se crée avec le code déjà formaté

---

## Étape 7 — Connexion OAuth

**Objectif :** ajouter un bouton "Se connecter avec Discord" sur la page de login.

### Ce qu'il faut faire

1. Installer Socialite + le provider Discord communautaire :

```bash
composer require laravel/socialite
composer require socialiteproviders/discord
```

> Discord n'est pas un driver natif de Socialite — le package `socialiteproviders/discord` est obligatoire.

2. Enregistrer le provider Discord dans `app/Providers/AppServiceProvider.php` :

```php
use SocialiteProviders\Manager\SocialiteWasCalled;

public function boot(): void
{
    \Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Discord\DiscordExtendSocialite::class);
}
```

3. Ajouter la colonne `oauth_id` sur la table `users` :

```bash
php artisan make:migration add_oauth_id_to_users_table --table=users
php artisan migrate
```

```php
// up()
$table->string('oauth_id')->nullable()->unique();

// down()
$table->dropColumn('oauth_id');
```

Ajouter `oauth_id` dans le `#[Fillable]` du modèle `User` :

```php
#[Fillable(['name', 'email', 'password', 'is_admin', 'oauth_id'])]
```

4. Configurer le fournisseur dans `config/services.php` :

```php
'discord' => [
    'client_id'     => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),
    'redirect'      => env('DISCORD_REDIRECT_URI'),
],
```

5. Créer l'application Discord et renseigner le `.env` :

- Sur https://discord.com/developers/applications → **New Application** → nom : `CineMap`
- Menu gauche **OAuth2** → copier `Client ID` et `Client Secret`
- Section **Redirects** → ajouter `http://localhost:8000/auth/discord/callback` → Save Changes

```
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI=http://localhost:8000/auth/discord/callback
```

6. Créer `SocialiteController` :

```bash
php artisan make:controller SocialiteController
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

public function redirectToDiscord()
{
    return Socialite::driver('discord')->stateless()->redirect();
}

public function handleDiscordCallback()
{
    $discordUser = Socialite::driver('discord')->stateless()->user();

    $user = User::where('oauth_id', $discordUser->getId())
        ->orWhere('email', $discordUser->getEmail())
        ->first();

    if ($user) {
        $user->update(['oauth_id' => $discordUser->getId()]);
    } else {
        $user = User::create([
            'oauth_id' => $discordUser->getId(),
            'name'     => $discordUser->getName(),
            'email'    => $discordUser->getEmail(),
            'password' => bcrypt(str()->random(32)),
        ]);
    }

    Auth::login($user);

    return redirect('/home');
}
```

> `->stateless()` est nécessaire pour éviter l'`InvalidStateException` en développement local.
>
> La logique `orWhere('email')` permet de lier un compte existant (créé par formulaire) à Discord sans doublon.

7. Ajouter les routes dans `routes/web.php` (hors tout middleware) :

```php
Route::get('/auth/discord', [SocialiteController::class, 'redirectToDiscord']);
Route::get('/auth/discord/callback', [SocialiteController::class, 'handleDiscordCallback']);
```

8. Ajouter le bouton sur la vue login (`resources/views/auth/login.blade.php`), séparé du formulaire par un séparateur "ou" :

```html
<div class="mt-6 flex items-center gap-3">
    <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
    <span class="text-sm text-gray-500 dark:text-gray-400">ou</span>
    <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
</div>

<div class="mt-4">
    <a href="/auth/discord"
       class="flex items-center justify-center gap-3 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition">
        Se connecter avec Discord
    </a>
</div>
```

---

## Étape 8 — Abonnement Stripe + API JSON protégée par JWT

**Objectif :** exposer une API JSON réservée aux utilisateurs abonnés et authentifiés par JWT.

### Route attendue

```
POST /api/auth/login       → retourne un token JWT
GET  /api/films/{film}/localisations   Authorization: Bearer <token>
```

---

### Partie A — Stripe (abonnement)

#### 1. Installer Cashier

```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

> Si `vendor:publish` est lancé deux fois, des migrations en double apparaissent. Supprimer manuellement les fichiers en double dans `database/migrations/` puis relancer `php artisan migrate:fresh --seed`.

> Si l'extension PHP `bcmath` est manquante : `sudo apt install php8.3-bcmath`

#### 2. Créer un compte Stripe et récupérer les clés

- Aller sur https://dashboard.stripe.com → créer un compte
- Menu gauche **Developers → API keys** → copier **Publishable key** et **Secret key** (mode test)

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

#### 3. Ajouter le trait `Billable` au modèle `User`

```php
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable, HasFactory, Notifiable;
}
```

#### 4. Créer un produit dans Stripe

- Dashboard Stripe → **Products → Add product**
- Nom : `Abonnement CineMap`, prix : `5.00 €/mois`, type **Recurring**
- Après création, copier le **Price ID** (`price_...`)

```env
STRIPE_PRICE_ID=price_...
```

#### 5. Créer le `SubscriptionController`

```bash
php artisan make:controller SubscriptionController
```

```php
use Illuminate\Http\Request;

public function index()
{
    return view('subscription.index', [
        'intent' => auth()->user()->createSetupIntent(),
    ]);
}

public function store(Request $request)
{
    $request->validate(['payment_method' => 'required']);

    $user = auth()->user();
    $user->createOrGetStripeCustomer();
    $user->addPaymentMethod($request->payment_method);
    $user->newSubscription('default', env('STRIPE_PRICE_ID'))
        ->create($request->payment_method);

    return redirect('/home')->with('success', 'Abonnement activé !');
}
```

Routes dans `web.php` (groupe `middleware('auth')`) :

```php
Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
```

#### 6. Tester

Carte de test Stripe : `4242 4242 4242 4242` — date future, CVC quelconque.

---

### Partie B — JWT (API protégée)

#### 1. Installer jwt-auth

```bash
composer require php-open-source-saver/jwt-auth
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

Le secret est ajouté automatiquement dans `.env` sous `JWT_SECRET`.

#### 2. Configurer le guard `api` dans `config/auth.php`

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'api' => ['driver' => 'jwt',     'provider' => 'users'],
],
```

#### 3. Implémenter `JWTSubject` sur le modèle `User`

```php
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
```

#### 4. Créer les controllers API

```bash
php artisan make:controller ApiAuthController
php artisan make:controller FilmApiController
```

**`ApiAuthController`** — vérifie les credentials ET l'abonnement avant de délivrer le token :

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (! $token = Auth::guard('api')->attempt($credentials)) {
        return response()->json(['error' => 'Identifiants invalides'], 401);
    }

    $user = Auth::guard('api')->user();

    if (! $user->subscribed('default')) {
        Auth::guard('api')->logout();
        return response()->json(['error' => 'Abonnement requis'], 403);
    }

    return response()->json(['token' => $token]);
}
```

**`FilmApiController`** — retourne le film et ses localisations en JSON :

```php
use App\Models\Film;

public function localisations(Film $film)
{
    return response()->json([
        'film'          => $film->only(['id', 'name', 'producer', 'release_year', 'synopsis']),
        'localisations' => $film->localisations()->get([
            'id', 'name', 'city', 'country', 'description', 'upvotes_count',
        ]),
    ]);
}
```

#### 5. Enregistrer `routes/api.php` dans `bootstrap/app.php`

> **Laravel 11+ ne charge pas `routes/api.php` automatiquement.** Sans cette étape, toutes les routes API retournent 404.

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',   // ← obligatoire
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    // ...
```

#### 6. Déclarer les routes dans `routes/api.php`

```php
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\FilmApiController;

Route::post('/auth/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/films/{film}/localisations', [FilmApiController::class, 'localisations']);
});
```

> Ne pas ajouter de middleware `subscribed` — il n'existe pas. La vérification de l'abonnement est faite dans `ApiAuthController@login` avant de délivrer le token.

#### 7. Tester avec curl

```bash
curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"ton@email.com","password":"motdepasse"}' | jq .

# Puis avec le token :
curl -s http://localhost:8000/api/films/1/localisations \
  -H "Authorization: Bearer <token>" | jq .
```
---

## Étape 9 — Serveur MCP

### C'est quoi ?

**MCP** est un protocole qui permet à une IA d'**appeler des outils externes** de façon standardisée. Au lieu d'expliquer à l'IA **"voici les données de mon app"**, tu lui donnes des outils qu'elle peut appeler elle-même pour aller chercher les données.

**Analogie** : c'est comme une API REST, mais conçue pour être consommée par une IA plutôt que par un humain ou une app frontend.

### Architecture

```
Claude Desktop / Claude Code
        │
        │  protocole MCP (JSON-RPC via stdio ou HTTP)
        ▼
   Serveur MCP  ←── ton code (Node.js, Python, PHP...)
        │
        │  appels directs (DB, HTTP, fichiers...)
        ▼
   Ton application (CineMap)
```

Le serveur MCP expose des tools (outils). L'IA décide quand les appeler en fonction de la conversation.

### Les 3 primitives MCP

| Primitive | Rôle | Exemple
|---|---|
| Tools | Actions que l'IA peut déclencher | `list_films`, `get_localisations_for_film` |
| Resources | Données que l'IA peut lire | Un fichier, une page web |
| Prompts | Templates de prompts réutilisables | — |
Pour le TP, besoin que des Tools.

### Comment fonctionne un tool ?

Définir :
1. Un nom : list_films
2. Une description : "Retourne tous les films de CineMap" — l'IA lit ça pour savoir quand l'appeler
3. Un schéma JSON des paramètres attendus
4. Une fonction handler qui fait le vrai travail

Exemple de dialogue IA :
```
> Utilisateur : "Quels films sont disponibles sur CineMap ?"
> Claude : appelle list_films → reçoit la liste → répond avec les données
```

### MCP pour le TP

**Objectif :** exposer deux outils en lecture seule pour permettre à une IA d'interroger l'application.

#### Outils attendus

| Outil | Description |
|---|---|
| `list_films` | Retourne la liste de tous les films |
| `get_localisations_for_film` | Retourne les emplacements d'un film donné |

#### Ce qu'il faut faire

**script Node.js** qui appelle ton API Laravel existante (étape 8).

On a déjà :
- GET /api/films/{film}/localisations (protégé JWT)
- Une DB avec des films et des localisations
Il faut un pont MCP → API.

### Structure du projet MCP

```
cinemap-mcp/
├── package.json
└── index.js        ← le serveur MCP
```

`package.json`
```json
{
  "name": "cinemap-mcp",
  "type": "module",
  "scripts": { "start": "node index.js" },
  "dependencies": {
    "@modelcontextprotocol/sdk": "^1.0.0"
  }
}
```

Pour récupérer le token d'un user abonné :
```bash
curl -s -X POST http://localhost:8000/api/auth/login   -H "Content-Type: application/json"   -d '{"email":"test@example.com","password":"Test123!"}' | jq -r '.token'
```

`.env`
```env
CINEMAP_JWT_TOKEN=eyJ...
```

`index.js` — le serveur complet
```js
import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";

const BASE_URL = "http://localhost:8000";
const JWT_TOKEN = process.env.CINEMAP_JWT_TOKEN; // token d'un user abonné

const server = new McpServer({ name: "cinemap", version: "1.0.0" });

// Outil 1 — liste tous les films
server.tool("list_films", "Retourne la liste de tous les films CineMap", {}, async () => {
    const res = await fetch(`${BASE_URL}/api/films`);
    const data = await res.json();
    return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
});

// Outil 2 — localisations d'un film
server.tool(
    "get_localisations_for_film",
    "Retourne les emplacements de tournage d'un film CineMap",
    { film_id: z.number().describe("ID du film") },
    async ({ film_id }) => {
        const res = await fetch(`${BASE_URL}/api/films/${film_id}/localisations`, {
            headers: { Authorization: `Bearer ${JWT_TOKEN}` },
        });
        const data = await res.json();
        return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
    }
);

const transport = new StdioServerTransport();
await server.connect(transport);
```

### `api.php`

> `list_films` appelle une route publique à créer — `get_localisations_for_film` réutilise la route JWT de l'étape 8.

Dans `cinemap-app/routes/api.php`, ajouter une route publique (hors du groupe middleware(`auth:api`)) :
```php
Route::get('/films', [FilmApiController::class, 'index']);
```

dans `cinemap-app/app/Http/Controllers/FilmApiController.php`, ajouter une méthode index :
```php
public function index()
{
    return response()->json(
        Film::query()->orderBy('name')->get(['id', 'name', 'producer', 'release_year'])
    );
}
```

`api.php` final :
```php
Route::get('/films', [FilmApiController::class, 'index']);           // public
Route::post('/auth/login', [ApiAuthController::class, 'login']);     // public

Route::middleware('auth:api')->group(function () {
    Route::get('/films/{film}/localisations', [FilmApiController::class, 'localisations']);
});
```

### Transport : stdio vs HTTP

| Mode | Quand l'utiliser |
|---|---|
| stdio | Client local (Claude Desktop, Claude Code) — le client lance le process |
| HTTP/SSE | Serveur distant, API partagée |

Pour un TP en local → stdio (plus simple, pas besoin d'un port ouvert).

### Configurer Claude pour utiliser ton serveur

[PDF : Installation Claude Code sur Linux Mint](https://tools.ruggi.site/ClaudeCode_LinuxMint_Community_Guide.pdf)

Sur **Linux Mint**, le fichier de config Claude code est :

```
~/.config/Claude/settings.json
```

Si le fichier n'existe pas, le créer :

```bash
mkdir -p ~/.config/Claude
touch ~/.config/Claude/settings.json
```

Contenu du fichier :

```json
{
  "mcpServers": {
    "cinemap": {
      "command": "node",
      "args": ["/chemin/absolu/vers/cinemap-mcp/index.js"],
      "env": {
        "CINEMAP_JWT_TOKEN": "eyJ..."
      }
    }
  }
}


{
  "mcpServers": {
    "cinemap": {
      "command": "node",
      "args": ["~/Documents/B3dev/Php/B3dev-TP_framework_php/cinemap-mcp/index.js"],
      "env": {
        "CINEMAP_JWT_TOKEN": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3NzY5MzE1NzAsImV4cCI6MTc3NjkzNTE3MCwibmJmIjoxNzc2OTMxNTcwLCJqdGkiOiJCY0FpTkhTMGVjc3g4TmJ4Iiwic3ViIjoiMiIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.aqfx-IcazHUfJyRQ-widelhEebOc1lZawGnlD4lc4Fc"
      }
    }
  }
}
```

Puis **redémarrer Claude** pour que la config soit prise en compte. Un icône de prise apparaît dans l'interface si le serveur MCP est bien détecté.

> **Si node est installé via `nvm`**, Claude ne charge pas le `.bashrc` et ne trouve pas `node` dans le PATH. Utiliser le chemin absolu :
> ```bash
> which node   # ex : /home/user/.nvm/versions/node/v20.19.0/bin/node
> ```
> Puis dans la config :
> ```json
> "command": "/home/user/.nvm/versions/node/v20.19.0/bin/node"
> ```

### Installer claude code (linux)

```bash
curl -fsSL https://claude.ai/install.sh | bash

Setting up Claude Code...

✔ Claude Code successfully installed!        

  Version: 2.1.118

  localisation: ~/.local/bin/claude


  Next: Run claude --help to get started

✅ Installation complete!

```

Pour le lancer:
```bash
claude
```

### Tester : demander à Claude "liste les films de CineMap"

Dans Claude Code, taper :
- `List all films`
- ou : `Get localisations for film 1`

Claude va appeler le serveur MCP
L'API Laravel sera utilisée

```bash
❯ List all films 

● Bash(curl -s http://localhost:8000/api/films | python3 -m json.tool)
  ⎿  [
         {
             "id": 1,
     … +11 lines (ctrl+o to expand)

● ┌─────┬────────────┬───────────────────┬──────────────┐
  │ ID  │   Title    │     Producer      │ Release Year │
  ├─────┼────────────┼───────────────────┼──────────────┤
  │ 1   │ Inception  │ Christopher Nolan │ 2010         │
  ├─────┼────────────┼───────────────────┼──────────────┤
  │ 2   │ The Matrix │ The Wachowskis    │ 1999         │
  └─────┴────────────┴───────────────────┴──────────────┘

  2 films total.

❯ Get localisations for film 1

● Bash(TOKEN=$(grep CINEMAP_JWT_TOKEN /home/rusty/Documents/B3dev/Php/B3dev-TP_framework_php/cinemap-mcp/.env | cut -d= -f2) && curl -s http://localhost:8000/api/films…)
  ⎿  {
         "film": {
             "id": 1,
     … +16 lines (ctrl+o to expand)

● Inception — Filming localisations:

  ┌─────┬──────────────┬──────────┬─────────┬─────────┐
  │ ID  │     Name     │   City   │ Country │ Upvotes │
  ├─────┼──────────────┼──────────┼─────────┼─────────┤
  │ 1   │ Central Park │ New York │ USA     │ 0       │
  └─────┴──────────────┴──────────┴─────────┴─────────┘

❯ Get localisation for Matrix

● Bash(TOKEN=$(grep CINEMAP_JWT_TOKEN /home/rusty/Documents/B3dev/Php/B3dev-TP_framework_php/cinemap-mcp/.env | cut -d= -f2) && curl -s http://localhost:8000/api/films…)
  ⎿  {
         "film": {
             "id": 2,
     … +16 lines (ctrl+o to expand)

● The Matrix — Filming localisations:

  ┌─────┬──────────────┬───────┬─────────┬─────────┐
  │ ID  │     Name     │ City  │ Country │ Upvotes │
  ├─────┼──────────────┼───────┼─────────┼─────────┤
  │ 2   │ Eiffel Tower │ Paris │ France  │ 0       │
  └─────┴──────────────┴───────┴─────────┴─────────┘

──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
❯  
──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
  ? for shortcuts
```

---

## Étape 10 — Tests PHPunit

### C'est quoi ?

**PHPUnit** est le framework de test standard en PHP. **Pest** est une surcouche syntaxique installée par défaut avec Laravel Breeze — elle génère des tests plus lisibles tout en exécutant PHPUnit sous le capot.

Les tests s'assurent que les routes, controllers et règles métier fonctionnent comme prévu, notamment après un refactoring.

### Installation de Pest

**Dans ce projet, Pest est déjà installé** (ajouté automatiquement par Breeze). Pour le vérifier :

```bash
cat composer.json | grep pest
# "pestphp/pest": "^4.x",
# "pestphp/pest-plugin-laravel": "^4.x"
```

Si Pest n'est **pas** présent (projet sans Breeze), l'installer manuellement :

```bash
composer require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies
./vendor/bin/pest --init
```

La commande `--init` génère `tests/Pest.php` (le fichier de configuration global).

> Pest et PHPUnit coexistent : les tests existants au format PHPUnit (`extends TestCase`) continuent de fonctionner sans modification.

### Architecture

```
tests/
├── Feature/          ← tests d'intégration (HTTP, base de données)
│   ├── Auth/         ← tests Breeze (déjà présents)
│   └── Api/          ← tests à écrire pour l'API JSON
└── Unit/             ← tests unitaires (logique pure, sans HTTP)
```

| Type | Ce qu'il teste | Accès DB | Accès HTTP |
|---|---|---|---|
| Unit | Une méthode isolée | ✗ | ✗ |
| Feature | Une route complète | ✓ | ✓ |

Pour le TP : uniquement des tests **Feature** sur l'API.

### Configuration (déjà en place)

`phpunit.xml` configure l'environnement de test :

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="QUEUE_CONNECTION" value="sync"/>
```

- **SQLite in-memory** : base isolée, recréée à chaque test, aucun impact sur la DB de dev
- **Queue sync** : les jobs sont exécutés immédiatement (pas de worker nécessaire)

`tests/Pest.php` applique automatiquement `RefreshDatabase` à tous les tests Feature :

```php
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
```

`RefreshDatabase` recrée les migrations et relance les seeders entre chaque test — chaque test part d'une base propre.

### Les tests à écrire

Trois routes API à couvrir :

| Route | Cas à tester |
|---|---|
| `GET /api/films` | 200 + liste ordonnée + bons champs |
| `POST /api/auth/login` | 401 mauvais mdp, 403 non abonné, 200 + token |
| `GET /api/films/{id}/localisations` | 401 sans token, 200 avec token valide |

### Créer les fichiers de test

```bash
php artisan make:test Api/FilmApiTest
php artisan make:test Api/ApiAuthTest
php artisan make:test Api/FilmLocalisationsApiTest
```

### `tests/Feature/Api/FilmApiTest.php`

```php
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
```

### `tests/Feature/Api/ApiAuthTest.php`

```php
<?php

use App\Models\User;

test('login retourne 401 si les identifiants sont invalides', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'mauvais-mot-de-passe',
    ])->assertStatus(401);
});

test('login retourne 403 si l\'utilisateur n\'est pas abonné', function () {
    $user = User::factory()->create(['password' => bcrypt('Test123!')]);

    $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'Test123!',
    ])->assertStatus(403);
});

test('login retourne un token si l\'utilisateur est abonné', function () {
    $user = User::factory()->create(['password' => bcrypt('Test123!')]);

    // Créer un abonnement actif directement en DB (pas d'appel Stripe)
    $user->subscriptions()->create([
        'type'          => 'default',
        'stripe_id'     => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_test',
        'quantity'      => 1,
    ]);

    $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'Test123!',
    ])->assertOk()->assertJsonStructure(['token']);
});
```

> `$user->subscriptions()->create([...])` insère l'abonnement directement en base, sans passer par Stripe. La méthode `subscribed('default')` de Cashier vérifie uniquement le `stripe_status` en DB.

### `tests/Feature/Api/FilmLocalisationsApiTest.php`

```php
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
```

> `actingAs($user, 'api')` authentifie l'utilisateur sur le guard JWT sans générer de vrai token — c'est l'approche standard pour tester les routes protégées par JWT.

### Lancer les tests

```bash
# Tous les tests
php artisan test

# Uniquement les tests Api
php artisan test --filter Api

# Un fichier précis
php artisan test tests/Feature/Api/ApiAuthTest.php

# Avec détail de chaque test
php artisan test --verbose
```

Exemple de sortie :

```
   PASS  Tests\Feature\Api\FilmApiTest
  ✓ GET /api/films retourne 200
  ✓ GET /api/films retourne les films triés par nom
  ✓ GET /api/films retourne les bons champs

   PASS  Tests\Feature\Api\ApiAuthTest
  ✓ login retourne 401 si les identifiants sont invalides
  ✓ login retourne 403 si l'utilisateur n'est pas abonné
  ✓ login retourne un token si l'utilisateur est abonné

   PASS  Tests\Feature\Api\FilmlocalisationsApiTest
  ✓ GET /api/films/{film}/localisations retourne 401 sans token
  ✓ GET /api/films/{film}/localisations retourne 200 avec un utilisateur authentifié

  Tests:    8 passed
  Duration: 0.42s
```

### Erreurs fréquentes

| Erreur | Cause | Solution |
|---|---|---|
| `Table 'subscriptions' doesn't exist` | Migrations Cashier non chargées en test | Vérifier que `DB_CONNECTION=sqlite` charge bien toutes les migrations |
| `Class FilmFactory not found` | Factory manquante | Créer avec `php artisan make:factory FilmFactory --model=Film` |
| `Unauthenticated` sur une route protégée | Guard `api` ignoré | Utiliser `actingAs($user, 'api')` et non `actingAs($user)` |
| `Expected status 200 but received 500` | Exception non catchée | Ajouter `$this->withoutExceptionHandling()` pour voir l'erreur réelle |

---

## Étape 11 — CI CD 

**Objectif :** déployer automatiquement l'application sur le VPS de test à chaque push sur la branche `staging`, après validation du pipeline CI.

### Architecture

#### VPS

Le VPS fourni par la formation est configuré en multi-app selon la structure suivante :

`http://78.138.58.95/` → page d'accueil (`/var/www/home/index.html`)  
`http://78.138.58.95/collegelaboussole/` → College La Boussole  
`http://78.138.58.95/saintbarthvolley/` → SaintBarth Volley  
`http://78.138.58.95/lucky7/` → Lucky7  
`http://78.138.58.95/B3dev-TP_VUE/` → TP VUE  
`http://78.138.58.95/cinemap/` → ce projet (à déployer)

Les autres projets sont déjà dockerisés et déployés. Au moment de la mise en place de CineMap, le VPS tourne avec les containers suivants :

```
CONTAINER       IMAGE               PORTS
tp-vue-front    www-tp-vue-front    0.0.0.0:8080->80/tcp
tp-vue-api      www-tp-vue-api      0.0.0.0:3003->3000/tcp
sbv-api         www-sbv-api         0.0.0.0:3006->5000/tcp
sbv-front       www-sbv-front       0.0.0.0:3007->3000/tcp
lucky7-front    www-lucky7-front    0.0.0.0:3008->3000/tcp
lucky7-back     www-lucky7-back     0.0.0.0:3009->4000/tcp
clb-back        www-clb-back        0.0.0.0:3010->5000/tcp
clb-front       www-clb-front       0.0.0.0:3011->3000/tcp
mongo           mongo               27017/tcp (interne)
```

Le port `3012` est donc le prochain disponible pour CineMap. La convention de nommage est : image `www-<projet>`, container `<projet>` (ou `<projet>-<service>` si plusieurs containers par projet).

##### Création utilisateur & SSH

```bash
# Sur le VPS en root
adduser newuser
usermod -aG sudo newuser

# Sur le PC local
ssh-keygen -t ed25519 -C "newuser-vps"
ssh-copy-id newuser@IP_VPS
ssh newuser@IP_VPS  # test sans mot de passe
```

##### Nginx

```bash
sudo apt update && sudo apt install nginx -y
sudo systemctl enable nginx && sudo systemctl start nginx
```

##### Docker

```bash
sudo apt install -y docker.io docker-compose
sudo systemctl enable docker && sudo systemctl start docker
```

##### MongoDB

MongoDB tourne dans un conteneur Docker — pas d'installation sur le VPS.

- Se connecte au backend via le nom de service `mongo` (réseau Docker interne)
- Données persistées via volume `./data/mongo:/data/db`
- Survit à un `docker-compose down`

##### Structure des dossiers VPS

```
/var/www/
│
├── B3dev-TP_VUE/          ← TP Vue (front + api)
├── CollegeLaBoussole/     ← projet CLB (front + back)
├── Lucky7/                ← projet Lucky7 (front + back)
├── SaintBarthVolley/      ← projet SBV (front + api)
├── B3dev-TP_framework_php/← ce projet (à cloner)
│
├── data/
│   └── mongo/             ← volume MongoDB persistant
│
├── home/
│   └── index.html         ← page d'accueil avec liens vers toutes les apps
└── docker-compose.yml     ← tous les services centralisés ici
```

> Lors du déploiement de CineMap, ajouter le lien dans `/var/www/home/index.html` :
> ```html
> <a href="/cinemap/">CineMap</a>
> ```

##### Docker Compose

Le fichier `/var/www/docker-compose.yml` centralise tous les projets du VPS. Il ne faut pas le recréer — uniquement y **ajouter** le service `cinemap`.

Convention : on ne rebuild jamais tous les containers en même temps — toujours cibler le service concerné :

```bash
# Rebuild et restart d'un seul service
docker-compose -f /var/www/docker-compose.yml build cinemap
docker-compose -f /var/www/docker-compose.yml up -d cinemap
```

##### Nginx reverse proxy

Fichier `/etc/nginx/sites-available/vps` :

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/home;
    index index.html;

    # API
    localisation /B3dev-TP_VUE/api/ {
        rewrite ^/B3dev-TP_VUE/api/(.*)$ /api/$1 break;
        proxy_pass http://127.0.0.1:3003/api/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # Socket.IO — pas de trailing slash → chemin complet préservé
    localisation /B3dev-TP_VUE/socket.io/ {
        proxy_pass http://127.0.0.1:3003;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # Frontend — trailing slash → strip le préfixe
    localisation /B3dev-TP_VUE/ {
        proxy_pass http://127.0.0.1:8080/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    localisation / {
        try_files $uri $uri/ /index.html;
    }
}
```

> **Règle Nginx** : `proxy_pass http://host/` (trailing slash) supprime le préfixe. `proxy_pass http://host` (sans) le conserve. Socket.IO nécessite que `/B3dev-TP_VUE/socket.io/` soit préservé → pas de trailing slash.

Recharger après modification :
```bash
sudo nginx -t && sudo systemctl reload nginx
```

#### Structure

```
push → staging
    ├── CI (Pint + Pest)
    │       ↓ si OK
    └── Deploy (SSH → VPS)
            ├── git pull
            ├── docker-compose build cinemap
            └── docker-compose up -d cinemap
```

L'app sera accessible sur : `http://78.138.58.95/cinemap/`

---

### Partie A — Dockerfile Laravel

Créer `cinemap-app/deployment/Dockerfile` :

```dockerfile
FROM php:8.3-fpm-alpine

# Dépendances système
RUN apk add --no-cache \
    nginx supervisor sqlite sqlite-dev \
    libzip-dev icu-dev oniguruma-dev \
    nodejs npm

# Extensions PHP
RUN docker-php-ext-install pdo pdo_sqlite bcmath zip mbstring opcache intl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP (layer séparé pour le cache Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Dépendances Node (layer séparé)
COPY package.json package-lock.json ./
RUN npm ci

# Code source
COPY . .

# Finalisation Composer + build assets Vite
RUN composer dump-autoload --optimize \
    && npm run build \
    && rm -rf node_modules

# Répertoires et permissions
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache database \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database

# Config serveur
COPY deployment/nginx.conf /etc/nginx/nginx.conf
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY deployment/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
```

---

### Partie B — nginx (dans le container)

Créer `cinemap-app/deployment/nginx.conf` :

```nginx
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /tmp/nginx.pid;

events { worker_connections 1024; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    server {
        listen 80;
        server_name _;
        root /var/www/html/public;
        index index.php;
        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { log_not_found off; access_log off; }
        location = /robots.txt  { log_not_found off; access_log off; }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht { deny all; }
    }
}
```

---

### Partie C — supervisord (nginx + php-fpm dans le même container)

Créer `cinemap-app/deployment/supervisord.conf` :

```ini
[supervisord]
nodaemon=true
user=root
logfile=/dev/null
logfile_maxbytes=0

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

---

### Partie D — entrypoint

Créer `cinemap-app/deployment/entrypoint.sh` :

```sh
#!/bin/sh
set -e

# Créer le fichier SQLite si absent (premier démarrage)
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

# Migrations (--force requis hors env=local)
php artisan migrate --force

# Cache config + vues (pas route:cache — routes avec closures incompatibles)
php artisan config:cache
php artisan view:cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

> `route:cache` est intentionnellement absent : les routes avec des closures (ex. `/dashboard`, `/`) le font échouer.

---

### Partie E — Configuration VPS (une seule fois, manuelle)

Ces étapes sont faites **une fois** en SSH sur le VPS, pas automatisées.

#### 1. Cloner le dépôt sur le VPS

```bash
cd /var/www
git clone git@github.com:RustyRory/B3dev-TP_framework_php.git
cd B3dev-TP_framework_php
git checkout staging
```

> La branche `staging` doit exister sur GitHub avant de faire cette étape. La créer localement puis pousser :
> ```bash
> git checkout -b staging
> git push -u origin staging
> ```

Pour les mises à jour suivantes, c'est la pipeline GitHub Actions qui fait le `git pull` automatiquement — ce clone manuel n'est fait **qu'une seule fois**.

#### 2. Ajouter le service dans `/var/www/docker-compose.yml`

```yaml
cinemap:
  build:
    context: ./B3dev-TP_framework_php/cinemap-app
    dockerfile: deployment/Dockerfile
  image: www-cinemap
  container_name: cinemap
  ports:
    - "3012:80"
  volumes:
    - ./data/cinemap:/var/www/html/database   # persistance SQLite entre les déploiements
  env_file:
    - ./B3dev-TP_framework_php/cinemap-app/.env
  restart: unless-stopped
```

> `image: www-cinemap` suit la convention du VPS (`www-<projet>`). Sans cette ligne, Docker Compose génère un nom d'image automatique peu lisible.

#### 2. Ajouter le bloc nginx dans `/etc/nginx/sites-available/vps` et `/etc/nginx/sites-enabled/vps`

```nginx
location /cinemap/ {
    rewrite ^/cinemap/(.*)$ /$1 break;
    proxy_pass http://127.0.0.1:3012;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

Recharger nginx :
```bash
sudo nginx -t && sudo systemctl reload nginx
```

#### 3. Créer le `.env` sur le VPS

Créer `/var/www/B3dev-TP_framework_php/cinemap-app/.env` :

```env
APP_NAME=CineMap
APP_ENV=staging
APP_KEY=base64:...        # générer avec : php artisan key:generate --show
APP_DEBUG=false
APP_URL=http://78.138.58.95/cinemap
ASSET_URL=http://78.138.58.95/cinemap

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_PATH=/cinemap
QUEUE_CONNECTION=database
CACHE_STORE=database

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_PRICE_ID=price_...

DISCORD_CLIENT_ID=...
DISCORD_CLIENT_SECRET=...
DISCORD_REDIRECT_URI=http://78.138.58.95/cinemap/auth/discord/callback

JWT_SECRET=...            # générer avec : php artisan jwt:secret --show
```

> `APP_URL` et `ASSET_URL` avec le préfixe `/cinemap` sont indispensables : sans eux, les assets Vite et les redirections Laravel pointent vers la racine du VPS.

#### 4. Seeder (premier déploiement uniquement)

```bash
docker exec cinemap php artisan migrate:fresh --seed
```

---

### Partie F — GitHub Actions

#### `ci.yml` — s'exécute sur toutes les branches

Créer `.github/workflows/ci.yml` :

```yaml
name: CI

on:
  push:
    branches-ignore:
      - staging
  pull_request:
    branches: [main, staging, dev]

concurrency:
  group: ci-${{ github.ref }}
  cancel-in-progress: true

jobs:
  lint:
    name: Lint (Pint)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_sqlite, bcmath, mbstring, zip, intl
          coverage: none
      - run: composer install --no-interaction --prefer-dist --optimize-autoloader
        working-directory: cinemap-app
      - run: ./vendor/bin/pint --test
        working-directory: cinemap-app

  tests:
    name: Tests (Pest)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_sqlite, bcmath, mbstring, zip, intl
          coverage: none
      - run: composer install --no-interaction --prefer-dist --optimize-autoloader
        working-directory: cinemap-app
      - run: cp .env.example .env
        working-directory: cinemap-app
      - run: php artisan key:generate
        working-directory: cinemap-app
      - run: touch database/database.sqlite
        working-directory: cinemap-app
      - run: php artisan migrate --force
        working-directory: cinemap-app
      - run: php artisan test
        working-directory: cinemap-app
```

#### `deploy-staging.yml` — s'exécute uniquement sur `staging`

Créer `.github/workflows/deploy-staging.yml` :

```yaml
name: Deploy to VPS (staging)

on:
  push:
    branches: [staging]

concurrency:
  group: deploy-staging
  cancel-in-progress: true

jobs:
  lint:
    name: Lint (Pint)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_sqlite, bcmath, mbstring, zip, intl
          coverage: none
      - run: composer install --no-interaction --prefer-dist --optimize-autoloader
        working-directory: cinemap-app
      - run: ./vendor/bin/pint --test
        working-directory: cinemap-app

  tests:
    name: Tests (Pest)
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, pdo_sqlite, bcmath, mbstring, zip, intl
          coverage: none
      - run: composer install --no-interaction --prefer-dist --optimize-autoloader
        working-directory: cinemap-app
      - run: cp .env.example .env
        working-directory: cinemap-app
      - run: php artisan key:generate
        working-directory: cinemap-app
      - run: touch database/database.sqlite
        working-directory: cinemap-app
      - run: php artisan migrate --force
        working-directory: cinemap-app
      - run: php artisan test
        working-directory: cinemap-app

  deploy:
    name: Déploiement CineMap — staging
    runs-on: ubuntu-latest
    needs: [lint, tests]
    steps:
      - uses: actions/checkout@v4

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.VPS_SSH_KEY }}
          port: 22
          command_timeout: 30m
          script: |
            set -e
            echo "Déploiement CineMap — staging"

            REPO_DIR="/var/www/B3dev-TP_framework_php"
            COMPOSE_FILE="/var/www/docker-compose.yml"

            # Cloner ou mettre à jour le repo
            if [ ! -d "$REPO_DIR/.git" ]; then
              git clone https://github.com/${{ github.repository }}.git "$REPO_DIR"
              git -C "$REPO_DIR" checkout staging
            else
              git -C "$REPO_DIR" fetch origin staging
              git -C "$REPO_DIR" reset --hard origin/staging
            fi

            # Vérifier que le .env existe
            ENV_FILE="$REPO_DIR/cinemap-app/.env"
            if [ ! -f "$ENV_FILE" ]; then
              echo "Fichier .env manquant : $ENV_FILE"
              echo "Créez-le manuellement sur le VPS avant de déployer."
              exit 1
            fi

            # Rebuild et restart
            docker-compose -f "$COMPOSE_FILE" build cinemap
            docker-compose -f "$COMPOSE_FILE" up -d --remove-orphans cinemap
            docker image prune -f

            # Vérification
            docker ps --filter "name=cinemap" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
            echo "Déploiement terminé : http://78.138.58.95/cinemap/"
```

---

### Partie G — GitHub Secrets

Dans **Settings → Secrets and variables → Actions** du repo GitHub, ajouter :

| Secret | Valeur |
|---|---|
| `VPS_HOST` | `78.138.58.95` |
| `VPS_USER` | `rusty` |
| `VPS_SSH_KEY` | contenu de la clé privée SSH (`~/.ssh/id_rsa`) |

> Pour générer une clé SSH dédiée : `ssh-keygen -t ed25519 -C "github-actions"`, puis ajouter la clé **publique** dans `~/.ssh/authorized_keys` sur le VPS.

---

### Partie H — Utiliser le serveur MCP contre le VPS

Le serveur MCP tourne toujours **en local** (stdio — Claude Code le lance sur ta machine). Mais au lieu de pointer vers `localhost:8000`, il peut interroger l'app déployée sur le VPS.

#### Architecture

```
Ta machine locale
├── Claude Code  ──stdio──▶  node cinemap-mcp/index.js
│                                      │
│                               HTTP fetch
│                                      ▼
│                         http://78.138.58.95/cinemap  ← VPS (staging)
```

Aucune installation sur le VPS — seul `BASE_URL` et le token changent.

#### 1. Récupérer un token JWT depuis le VPS

```bash
curl -s -X POST http://78.138.58.95/cinemap/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!"}' | jq -r '.token'
```

> L'utilisateur `test@example.com` doit exister et être abonné sur la DB du VPS (créé via `docker exec cinemap php artisan migrate:fresh --seed`).

#### 2. Mettre à jour `cinemap-mcp/index.js`

Changer `BASE_URL` pour pointer vers le VPS :

```js
const BASE_URL = "http://78.138.58.95/cinemap";
```

> Pour switcher facilement entre dev et staging, utiliser une variable d'environnement :
> ```js
> const BASE_URL = process.env.CINEMAP_BASE_URL ?? "http://localhost:8000";
> ```

#### 3. Mettre à jour `~/.config/Claude/settings.json`

```json
{
  "mcpServers": {
    "cinemap": {
      "command": "/home/rusty/.nvm/versions/node/v20.19.0/bin/node",
      "args": ["/path/.../B3dev-TP_framework_php/cinemap-mcp/index.js"],
      "env": {
        "CINEMAP_BASE_URL": "http://78.138.58.95/cinemap",
        "CINEMAP_JWT_TOKEN": "eyJ..."
      }
    }
  }
}
```

Redémarrer Claude Code pour que la nouvelle config soit prise en compte.

#### 4. Tester

Dans Claude Code :
```
List all films
Get localisations for film 1
```

Claude appelle `list_films` → `GET http://78.138.58.95/cinemap/api/films`  
Claude appelle `get_localisations_for_film` → `GET http://78.138.58.95/cinemap/api/films/1/localisations`

---

### Checklist

- [ ] Fichiers `deployment/` créés dans `cinemap-app/`
- [ ] Service `cinemap` ajouté dans `/var/www/docker-compose.yml`
- [ ] Bloc nginx `/cinemap/` ajouté et nginx rechargé
- [ ] `.env` créé sur le VPS avec `APP_URL` et `ASSET_URL` corrects
- [ ] GitHub Secrets `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` configurés
- [ ] Push sur `staging` → pipeline CI vert → déploiement automatique
- [ ] `http://78.138.58.95/cinemap/` accessible




