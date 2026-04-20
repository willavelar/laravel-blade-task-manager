#!/bin/sh
set -e

mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite

php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
