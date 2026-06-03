#!/usr/bin/env sh
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ ! -d vendor/laravel/framework ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p \
  storage/app/private \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ -f .env ] && grep -q '^APP_KEY=$' .env; then
  php artisan key:generate --ansi
fi

php artisan package:discover --ansi >/dev/null

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec "$@"
