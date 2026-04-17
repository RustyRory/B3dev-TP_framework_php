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

1. Installer Socialite :

```bash
composer require laravel/socialite
```

2. Ajouter la colonne `oauth_id` sur la table `users` (pour retrouver l'utilisateur au second login) :

```bash
php artisan make:migration add_oauth_id_to_users_table --table=users
```

```php
$table->string('oauth_id')->nullable()->unique();
```

3. Configurer le fournisseur dans `config/services.php` :

```php
'discord' => [
    'client_id'     => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),
    'redirect'      => env('DISCORD_REDIRECT_URI'),
],
```

4. Créer l'application Discord :

- Sur https://discord.com/developers/applications
- Cliquer sur `New Application` → nom : `CineMap`
- Dans le menu gauche, aller dans OAuth2
  - `Client ID` → copier cette valeur dans `DISCORD_CLIENT_ID`
  - `Client Secret` → cliquer sur `Reset Secret` → copier dans `DISCORD_CLIENT_SECRET`
- Dans la section "Redirects", cliquer sur `Add Redirect` et ajouter :
  - `http://localhost:8000/auth/discord/callback`
  - Puis Save Changes.

5. Ajouter les variables dans `.env` :

```
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI=http://localhost:8000/auth/discord/callback
```

5. Créer un controller dédié `SocialiteController` :

```bash
php artisan make:controller SocialiteController
```

Avec deux méthodes :

```php
use Laravel\Socialite\Facades\Socialite;

public function redirectToDiscord()
{
    return Socialite::driver('discord')->redirect();
}

public function handleDiscordCallback()
{
    $discordUser = Socialite::driver('discord')->user();

    $user = User::firstOrCreate(
        ['oauth_id' => $discordUser->getId()],
        [
            'name'     => $discordUser->getName(),
            'email'    => $discordUser->getEmail(),
            'password' => bcrypt(str()->random(32)),
        ]
    );

    Auth::login($user);

    return redirect('/home');
}
```

6. Ajouter les routes OAuth dans `routes/web.php` (hors middleware `auth`) :

```php
Route::get('/auth/discord', [SocialiteController::class, 'redirectToDiscord']);
Route::get('/auth/discord/callback', [SocialiteController::class, 'handleDiscordCallback']);
```

7. Ajouter le bouton sur la vue login (`resources/views/auth/login.blade.php`) :

```html
<a href="/auth/discord"
   class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
    Se connecter avec Discord
</a>
```

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

## En plus

- Tests
- github actions
   - Deploiement auto env test
   - cicd


