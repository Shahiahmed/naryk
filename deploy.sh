#!/usr/bin/env bash
#
# Deploys naryk.kz. Run as root on the VPS; the GitHub workflow calls it over
# SSH on every push to main.
#
set -euo pipefail

APP_DIR=/var/www/naryk
export COMPOSER_ALLOW_SUPERUSER=1

cd "$APP_DIR"

echo "==> Pulling main"
git fetch --prune origin
git reset --hard origin/main

echo "==> Installing dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# No `php artisan migrate` on purpose.
#
# This project has no migrations: the schema belongs to the client and must not
# change. `database/migrations/` holds only a .gitkeep. Running migrate would be
# a no-op today, but it would quietly become a way to alter their live database
# the day somebody adds a file there. If a schema change ever is agreed, run it
# by hand, with a dump taken first.

echo "==> Publishing Filament assets"
php artisan filament:optimize-clear
php artisan filament:assets
php artisan filament:optimize

echo "==> Caching config and views"
php artisan config:clear
php artisan config:cache
php artisan view:clear
php artisan view:cache
# `route:cache` is skipped, per the server memo.

if [ ! -L public/storage ]; then
    echo "==> Linking storage"
    php artisan storage:link
fi

echo "==> Fixing ownership"
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 storage bootstrap/cache
chmod 640 .env

echo "==> Reloading PHP-FPM"
systemctl reload php8.3-fpm

echo "==> Done"
