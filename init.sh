#!/usr/bin/env bash


# make directories in storage.
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/testing
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs

# run migration.
cd /var/www
php artisan passport:keys
php artisan migrate --seed
php /init.php
