# ---- Stage 1: build front-end ----
FROM node:22-alpine AS frontend
# Env Reverb harus di-bake ke bundle saat build (Vite hanya baca saat build, bukan runtime)
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=80
ARG VITE_REVERB_SCHEME=http
ARG VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST VITE_REVERB_PORT=$VITE_REVERB_PORT VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources/ ./resources/
RUN npm run build

# ---- Stage 2: nginx distroless dengan asset baked-in ----
FROM cgr.dev/chainguard/nginx:latest AS nginx
COPY public/index.php public/favicon.ico public/robots.txt public/.htaccess /var/www/public/
COPY --from=frontend /app/public/build /var/www/public/build

# ---- Stage 3: PHP app ----
FROM php:8.2-fpm-alpine AS app

# Ekstensi PHP yang dipakai Laravel + MySQL
# pcntl wajib untuk Reverb (signal handling SIGINT/SIGTERM)
RUN apk add --no-cache $PHPIZE_DEPS curl \
    && docker-php-ext-install pdo_mysql bcmath opcache pcntl \
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
