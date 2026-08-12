#!/bin/sh
set -e

echo "Putting application into maintenance mode..."
# We use cache of db to sync with all related services (fpm, worker, migrator)
# || true is used to avoid the script from failing if the application is starting the first time without any tables. Remember! To use maintanace mode we need the cache table to be created.
php artisan down --refresh=10 --secret="secret_NYUWNKPOOPXYUY46UNKOO5368299_isra36OOhnnzc624760100" || true

echo "Running Migrations..."
php artisan migrate --force

echo "Bringing application back up..."
php artisan up || true

echo "Migration process completed."