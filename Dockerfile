# ---- Stage 1: build front-end ----
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources/ ./resources/
RUN npm run build

# ---- Stage 2: nginx distroless dengan asset baked-in ----
FROM cgr.dev/chainguard/nginx:latest
COPY --from=frontend /app/public/build /var/www/public/build

# ---- Stage 3: PHP app ----
FROM php:8.2-fpm-alpine AS app

# Ekstensi PHP yang dipakai Laravel + MySQL
RUN apk add --no-cache $PHPIZE_DEPS curl \
    && docker-php-ext-install pdo_mysql bcmath opcache \
    && apk del $PHPIZE_DEPS

WORKDIR /var/www/html

# Composer dari image resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install vendor dulu (layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Copy sisanya
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Permission + direktori runtime yang di-exclude .dockerignore
# config:cache tidak bisa di build-time (env belum ada); dijalankan migrate service saat start
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
