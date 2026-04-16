# CineMap — TP Laravel

Application de gestion de lieux de tournage de films, réalisée dans le cadre d'un TP Laravel à MyDigitalSchool.

---

## À propos

**CineMap** permet de gérer des emplacements de tournage associés à des films.

- Un visiteur peut s'inscrire et se connecter (auth classique ou OAuth).
- Un utilisateur connecté peut consulter les films et ajouter des emplacements de tournage.
- Un administrateur peut tout modifier.

### Modèle de données

| Modèle | Champs principaux |
|---|---|
| `Film` | `title`, `release_year`, `synopsis` |
| `Location` | `film_id`, `user_id`, `name`, `city`, `country`, `description`, `upvotes_count` |
| `location_votes` | `user_id`, `location_id`, `created_at` |
| `User` | champs auth standard + `is_admin` |

---

## Fonctionnalités

- Authentification (inscription / connexion / déconnexion)
- Connexion OAuth (Google / GitHub / Facebook)
- CRUD Films (admin)
- CRUD Locations (utilisateur connecté, admin pour tout modifier)
- Système d'upvotes sur les emplacements (1 vote par utilisateur par emplacement)
- Middleware administrateur (`is_admin`)
- Queue & Jobs (recalcul des `upvotes_count` en arrière-plan)
- Commande Artisan planifiée (suppression des locations > 14 jours avec < 2 upvotes)
- API JSON protégée par abonnement Stripe + authentification JWT (`/api/films/{film}/locations`)
- Serveur MCP en lecture seule (`list_films`, `get_locations_for_film`)

---

## Installation

Consultez le guide complet : [INSTALL.md](INSTALL.md)

### Démarrage rapide

```bash
git clone git@github.com:RustyRory/B3dev-TP_framework_php.git && cd B3dev-TP_framework_php
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

---

## Structure du projet

```
app/
 ├── Http/
 │   ├── Controllers/
 │   └── Middleware/
 ├── Jobs/
 ├── Console/Commands/
 └── Models/
resources/
 └── views/
routes/
 ├── web.php
 └── api.php
database/
 ├── migrations/
 └── seeders/
```

---

## Commandes utiles

```bash
php artisan serve                         # Lancer le serveur
php artisan migrate                       # Lancer les migrations
php artisan migrate:fresh --seed          # Reset BDD + seeders
php artisan queue:listen                  # Lancer le worker de queue
php artisan app:clean-old-locations       # Suppression locations obsolètes (test manuel)
./vendor/bin/pint                         # Formater le code
```

---

## Contribuer

Consultez [CONTRIBUTING.md](CONTRIBUTING.md) pour les conventions et le workflow de contribution.

---

## Licence

Distribué sous licence MIT. Voir [LICENSE](LICENSE) pour plus d'informations.

---

## Auteur

Projet réalisé dans le cadre d'un TP Laravel — MyDigitalSchool.
