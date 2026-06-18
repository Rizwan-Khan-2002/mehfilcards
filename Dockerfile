# ── MehfilCards (Laravel 12) production image ─────────────────────────────
# Served with Apache + mod_php (multi-process), which is reliable behind the
# Render/Railway proxy — unlike `php artisan serve`, which is single-threaded
# and hangs under a load balancer. The app's CSS/JS are static files in
# public/, so no Node/Vite build is needed. SQLite is the default DB so no
# separate DB service is required; set DB_CONNECTION=mysql + DB_* to use MySQL.

FROM php:8.2-apache

# System libraries + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip pdo pdo_mysql pdo_sqlite mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

# Apache: enable URL rewriting, point docroot at Laravel's public/, allow .htaccess
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

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
    && chown -R www-data:www-data storage bootstrap/cache database public/uploads \
    && chmod -R 775 storage bootstrap/cache database public/uploads

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite

EXPOSE 80

ENTRYPOINT ["sh", "/var/www/html/docker-entrypoint.sh"]
