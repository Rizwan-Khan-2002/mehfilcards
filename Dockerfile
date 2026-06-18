# ── MehfilCards (Laravel 12) production image ─────────────────────────────
# The app's CSS/JS are static files in public/, so no Node/Vite build is
# required. We only need PHP 8.2 with GD (for server-side PNG/QR rendering)
# and a database driver. SQLite is used by default so no separate DB service
# is needed; set DB_CONNECTION=mysql + DB_* env vars to use MySQL instead.

FROM php:8.2-cli

# System libraries + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip pdo pdo_mysql pdo_sqlite mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Application code
COPY . .
RUN composer dump-autoload --optimize --no-interaction

# Writable dirs + default SQLite database
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
        bootstrap/cache database public/uploads/templates \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database public/uploads

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite

EXPOSE 8000

ENTRYPOINT ["sh", "/app/docker-entrypoint.sh"]
