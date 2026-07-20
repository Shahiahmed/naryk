#!/usr/bin/env bash
#
# Production deploy for the FASTPANEL client server.
# Install once as root (do NOT rely on the copy inside the git tree at runtime):
#
#   install -o root -g root -m 755 deploy/fastpanel-naryk.sh /usr/local/sbin/naryk-deploy.sh
#
# GitHub Actions SSH must be forced to: sudo /usr/local/sbin/naryk-deploy.sh
# So a compromised push cannot change what the deploy key is allowed to run.
#
set -euo pipefail

# --- locked to this site only; never auto-detect other vhosts ---
readonly APP_DIR=/var/www/naryk_kz_usr/data/www/naryk.kz
readonly APP_USER=naryk_kz_usr
readonly APP_GROUP=naryk_kz_usr
readonly PHP=/opt/php83/bin/php
readonly COMPOSER=/usr/local/bin/composer
readonly EXPECTED_ORIGIN_SUBSTRING='Shahiahmed/naryk'

export COMPOSER_ALLOW_SUPERUSER=1

if [[ "$(id -u)" -ne 0 ]]; then
    echo "This script must run as root (via sudo from the deploy user)." >&2
    exit 1
fi

if [[ ! -d "$APP_DIR/.git" ]]; then
    echo "Refusing to deploy: $APP_DIR is not a git checkout." >&2
    exit 1
fi

if ! git config --global --get-all safe.directory 2>/dev/null | grep -qx "$APP_DIR"; then
    git config --global --add safe.directory "$APP_DIR"
fi

cd "$APP_DIR"

origin_url="$(git remote get-url origin)"
if [[ "$origin_url" != *"$EXPECTED_ORIGIN_SUBSTRING"* ]]; then
    echo "Refusing to deploy: unexpected origin '$origin_url'" >&2
    exit 1
fi

echo "==> Deploying $APP_DIR only (other vhosts untouched)"

echo "==> Pulling main"
git fetch --prune origin
git reset --hard origin/main

echo "==> Installing dependencies"
"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# No migrate — client schema must not change.

echo "==> Filament + caches"
"$PHP" artisan filament:optimize-clear
"$PHP" artisan filament:assets
"$PHP" artisan filament:optimize
"$PHP" artisan config:clear
"$PHP" artisan config:cache
"$PHP" artisan view:clear
"$PHP" artisan view:cache

if [[ ! -L public/storage ]]; then
    "$PHP" artisan storage:link
fi

echo "==> Permissions (app dir only)"
chown -R "$APP_USER:$APP_GROUP" "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod 640 "$APP_DIR/.env"

echo "==> Reload PHP for this stack (best-effort, no other sites reconfigured)"
reloaded=0
for service in php83-php-fpm php83-fpm php-fpm httpd; do
    if systemctl is-active --quiet "$service" 2>/dev/null; then
        systemctl reload "$service"
        echo "Reloaded $service"
        reloaded=1
        break
    fi
done
if [[ "$reloaded" -eq 0 ]]; then
    echo "No php-fpm/httpd reload needed or service name differs."
fi

echo "==> Done"
