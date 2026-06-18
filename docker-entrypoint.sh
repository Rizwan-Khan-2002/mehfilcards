#!/bin/sh
# Startup script for the MehfilCards container.
set -e
cd /app

# Ensure an environment file exists (platform env vars still take priority).
[ -f .env ] || cp .env.example .env

# Generate an app key if one was not supplied via the APP_KEY env var.
if [ -z "$APP_KEY" ] && ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Make sure the SQLite database file exists when using the sqlite driver.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    : "${DB_DATABASE:=/app/database/database.sqlite}"
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
fi

php artisan config:clear
php artisan migrate --force

# Bind to the port the host assigns (Render/Railway set $PORT), default 8000.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
