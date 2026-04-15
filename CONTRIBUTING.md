# Contribuer à CineMap

Merci de l'intérêt porté à ce projet ! Ce document explique comment contribuer.

---

## Prérequis

Avant de commencer, assurez-vous d'avoir suivi le guide d'installation : [INSTALL.md](INSTALL.md).

---

## Workflow de contribution

### 1. Forker et cloner

```bash
git clone https://github.com/<votre-pseudo>/B3dev-TP_framework_php.git
cd B3dev-TP_framework_php
```

### 2. Créer une branche

Nommez votre branche selon le type de contribution :

```bash
git checkout -b feat/ma-fonctionnalite
git checkout -b fix/correction-bug
git checkout -b docs/mise-a-jour-readme
```

### 3. Développer

- Respectez l'architecture MVC existante (Models / Controllers / Views Blade).
- Créez une migration pour tout changement de schéma.
- Validez les données côté serveur (Form Requests Laravel).

### 4. Formater le code

Avant de commiter, lancez Laravel Pint :

```bash
./vendor/bin/pint
```

### 5. Commiter

```bash
git add .
git commit -m "feat: description courte de la fonctionnalité"
```

Formats de message conseillés :

| Préfixe | Usage |
|---|---|
| `feat:` | Nouvelle fonctionnalité |
| `fix:` | Correction de bug |
| `docs:` | Documentation uniquement |
| `refactor:` | Refactoring sans changement fonctionnel |
| `test:` | Ajout ou modification de tests |

### 6. Pousser et ouvrir une Pull Request

```bash
git push origin feat/ma-fonctionnalite
```

Ouvrez ensuite une Pull Request sur GitHub vers la branche `main`.

---

## Conventions de code

- PHP >= 8.x, syntaxe moderne (typed properties, match, etc.)
- Noms de classes en `PascalCase`, méthodes et variables en `camelCase`
- Routes nommées et organisées dans `routes/web.php` / `routes/api.php`
- Pas de logique métier dans les vues Blade

---

## Signaler un bug

Ouvrez une [issue GitHub](../../issues) en précisant :

- la version PHP / Laravel utilisée
- les étapes pour reproduire le bug
- le comportement attendu vs observé

---

## Licence

En contribuant, vous acceptez que vos modifications soient distribuées sous la licence [MIT](LICENSE).
