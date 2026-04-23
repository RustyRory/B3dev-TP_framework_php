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