#!/bin/sh

set -e

if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.docker /var/www/.env
fi

chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

composer dump-autoload --optimize

php artisan key:generate --no-interaction --force
php artisan jwt:secret --no-interaction --force

echo "Waiting for database..."

until php artisan migrate --force --no-interaction >/dev/null 2>&1; do
    echo "Database is unavailable - retrying in 2 seconds..."
    sleep 2
done

echo "Database is ready."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

exec php-fpm