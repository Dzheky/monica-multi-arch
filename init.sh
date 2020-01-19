#!/usr/bin/env bash

# make directories in storage.
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/testing
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/avatars

# make sure ownership is OK.
chown -R www-data:www-data /var/www
chmod 775 /var/www/storage

# run migration.
cd /var/www
php artisan passport:keys
php artisan migrate --seed
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan storage:link
php /init.php
