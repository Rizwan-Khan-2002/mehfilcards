#!/bin/sh
# Startup script for the MehfilCards container (Apache + mod_php).
set -e
cd /var/www/html

# Ensure an environment file exists (platform env vars still take priority).
[ -f .env ] || cp .env.example .env

# Generate an app key if one was not supplied via the APP_KEY env var.
if [ -z "$APP_KEY" ] && ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Make sure the SQLite database file exists when using the sqlite driver.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    : "${DB_DATABASE:=/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_DATABASE")"
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
    chown -R www-data:www-data "$(dirname "$DB_DATABASE")" 2>/dev/null || true
fi

php artisan config:clear
php artisan migrate --force

# Seed categories, card templates, the demo invitation and an admin user.
# The seeder is idempotent (updateOrCreate), so it is safe to run on every
# boot — important because the free-tier SQLite database is ephemeral.
php artisan db:seed --force || true

# Make Apache listen on the port the host assigns (Render/Railway set $PORT).
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
