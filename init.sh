#!/usr/bin/env bash

cd /var/www
php artisan migrate --seed
php /init.php
