#!/bin/sh
set -e

# Fix permissions for storage and database so www-data (php-fpm) can write
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ -f /var/www/html/database/database.sqlite ]; then
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

chown www-data:www-data /var/www/html/database
chmod 775 /var/www/html/database

exec "$@"
