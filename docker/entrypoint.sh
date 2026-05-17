#!/bin/sh
set -e

php artisan config:clear

if [ -n "$APP_KEY" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec php artisan serve --host=0.0.0.0 --port=3020
