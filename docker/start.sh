#!/bin/sh
set -e
cd /var/www/html

# Render tells us which port to listen on through $PORT
PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# The disk is wiped on every deploy/restart, so put the demo photos back.
# (The database already stores their paths.)
mkdir -p storage/app/public/services
cp -r database/seeders/assets/services/. storage/app/public/services/
chown -R www-data:www-data storage bootstrap/cache

# Create any missing tables; does nothing if the schema is up to date
php artisan migrate --force

# Cache config/routes/views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground