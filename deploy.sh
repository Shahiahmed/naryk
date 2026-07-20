#!/usr/bin/env bash
#
# Contabo / generic VPS deploy (in-repo). For the FASTPANEL client server use
# /usr/local/sbin/naryk-deploy.sh instead (see deploy/fastpanel-naryk.sh) so the
# GitHub deploy key cannot execute a mutable script from git and cannot touch
# other vhosts.
#
set -euo pipefail

export COMPOSER_ALLOW_SUPERUSER=1

APP_DIR=/var/www/naryk
APP_USER=www-data
PHP=php
COMPOSER=composer

if [[ ! -d "$APP_DIR/.git" ]]; then
    echo "Refusing to deploy: $APP_DIR is not a git checkout." >&2
    exit 1
fi

if ! git config --global --get-all safe.directory 2>/dev/null | grep -qx "$APP_DIR"; then
    git config --global --add safe.directory "$APP_DIR"
fi

cd "$APP_DIR"

echo "==> Pulling main"
git fetch --prune origin
git reset --hard origin/main

echo "==> Installing dependencies"
"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# No `php artisan migrate` on purpose — client schema must not change.

echo "==> Publishing Filament assets"
"$PHP" artisan filament:optimize-clear
"$PHP" artisan filament:assets
"$PHP" artisan filament:optimize

echo "==> Caching config and views"
"$PHP" artisan config:clear
"$PHP" artisan config:cache
"$PHP" artisan view:clear
"$PHP" artisan view:cache

if [[ ! -L public/storage ]]; then
    echo "==> Linking storage"
    "$PHP" artisan storage:link
fi

echo "==> Fixing ownership (app dir only)"
chown -R "$APP_USER:$APP_USER" "$APP_DIR"
chmod -R 775 storage bootstrap/cache
chmod 640 .env

echo "==> Reloading PHP-FPM"
systemctl reload php8.3-fpm

echo "==> Done"
