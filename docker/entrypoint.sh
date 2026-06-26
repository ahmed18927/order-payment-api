#!/bin/bash

# Copy .env if not exists
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.docker /var/www/.env
fi

# Generate app key if empty
php artisan key:generate --no-interaction --force

# Generate JWT secret if empty
php artisan jwt:secret --no-interaction --force

# Wait for DB then migrate
echo "Waiting for database..."
sleep 5
php artisan migrate --force --no-interaction

# Clear caches
php artisan config:clear
php artisan cache:clear

php-fpm