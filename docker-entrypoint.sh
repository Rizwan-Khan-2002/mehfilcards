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
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE" 2>/dev/null || true
fi

php artisan config:clear
php artisan migrate --force

# Make Apache listen on the port the host assigns (Render/Railway set $PORT).
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
