#!/bin/sh
set -e

cd /var/www/html

# Bootstrap .env from example if missing
[ -f .env ] || cp .env.example .env

# Create SQLite database and storage directories if needed
mkdir -p database storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
[ -f database/database.sqlite ] || touch database/database.sqlite

# Generate APP_KEY if not set (neither in env var nor in .env)
if [ -z "$APP_KEY" ] && grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

# Fix permissions so www-data (php-fpm) can write
chown -R www-data:www-data storage database bootstrap/cache
chmod -R 775 storage database bootstrap/cache

# Run migrations (safe to run on every start)
php artisan migrate --force

# Create storage symlink
php artisan storage:link 2>/dev/null || true

# Cache config/routes/views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
