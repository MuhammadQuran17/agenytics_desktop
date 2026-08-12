#!/bin/sh
set -e

echo "Optimization started..."

php artisan optimize:clear
php artisan optimize

php artisan storage:link

echo "Optimization success..."

exec "$@"