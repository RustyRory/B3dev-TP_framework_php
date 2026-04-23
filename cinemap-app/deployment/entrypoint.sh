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
