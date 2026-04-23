# LARAVEL

## Installation

### Installer via composer 

```bash
composer create-project laravel/laravel my-app
```

---

### Installer via laravel installer

```bash
composer global require laravel/installer
laravel new example-app
```

---

### Installation des dépendances node

```bash
npm install
```

---

### Installation de la base de données

```bash
php artisan migrate
```

> Quand il vous demande de créer un fichier de BDD, dites "oui"

---

### Mise en place de la clé de cryptage de l'application

```bash
php artisan key:generate
```

---

### Lancement du server Laravel

```bash
composer run dev
```

> En cas de bug avec Composer, RDV dans votre fichier composer.json et modifiez la ligne :

```bash
npx concurrently
    -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\"
    \"php artisan serve\"
    \"php artisan queue:listen --tries=1 --timeout=0\"
    \"php artisan pail --timeout=0\"
    \"npm run dev\" --names=server,queue,logs,vite --kill-others"
```

> Si vous avez une erreur `"pail"`, supprimez la partie `\"php artisan pail --timeout=0\"`
> Vous pouvez aussi virer la partie `-c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\"`

Tout devrait refonctionner par la suite, et vous devriez avoir :

```bash
npx concurrently \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
```

### Mise en place de l'authentification

```bash
composer require laravel/breeze --dev
php artisan breeze:install
php artisan migrate
npm install
composer run dev
```

> Si vous avez une erreur, rendez-vous dans le fichier `/resources/js/app.js` et supprimez la ligne import `./bootstrap;`

## Workflow ressource

### 1. Migration
```bash
php artisan make:migration nom_de_la_migration
```

### 2. Model
```bash
php artisan make:model Nom
```

### 3. Factory & Seeder
```bash
php artisan make:factory NomFactory
php artisan make:seeder NomSeeder
```

N'oubliez pas d'appeler votre seeder dans `database/seeders/DatabaseSeeder`

N'oubliez pas de reset la BDD avec les nouveaux seeders `php artisan migrate:fresh --seed`

> A partir d'ici, vous pouvez faire les étapes 1 par une pour chaque route / fonctionnalité

### 4. Route

Créer la route dans `routes/web.php`


### 5. Controller

Si besoin, créez le controller :
```bash
php artisan make:controller NomController
```

N'oubliez pas de créer la méthode mentionnée dans la route :
  - index
  - create
  - store
  - edit
  - update
  - destroy 
  - ou toute autre route nécessaire

N'oubliez pas aussi d'appeler les données via les Models si vous en avez besoin ici.

### 6. Views

Créez l'architecture de dossiers / fichiers nécessaires pour vos vues dans `resources/views`.

En cas de besoin pour le moteur de template, Laravel utilise Blade. Pour fonctionner, tous les fichiers de vues doivent avoir l'extension `.blade.php`