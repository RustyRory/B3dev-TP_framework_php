# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY cinemap-app/package*.json ./
RUN npm ci
COPY cinemap-app/ .
RUN npm run build

# Stage 2: PHP application
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    unzip \
    oniguruma-dev \
    && docker-php-ext-install pdo_sqlite pdo_mysql gd zip bcmath mbstring opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (separate layer for cache)
COPY cinemap-app/composer.json cinemap-app/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY cinemap-app/ .

# Copy compiled frontend assets
COPY --from=frontend /app/public/build public/build

# Copy runtime config
COPY deployment/nginx.conf /etc/nginx/http.d/default.conf
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY deployment/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
