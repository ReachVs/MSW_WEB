#!/usr/bin/env sh
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

set_env_value() {
  key="$1"
  value="$2"

  if [ -z "$value" ]; then
    return
  fi

  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    printf '%s=%s\n' "$key" "$value" >> .env
  fi
}

set_env_value APP_URL "$APP_URL"
set_env_value DB_CONNECTION "$DB_CONNECTION"
set_env_value DB_HOST "$DB_HOST"
set_env_value DB_PORT "$DB_PORT"
set_env_value DB_DATABASE "$DB_DATABASE"
set_env_value DB_USERNAME "$DB_USERNAME"
set_env_value DB_PASSWORD "$DB_PASSWORD"
set_env_value CACHE_STORE "$CACHE_STORE"
set_env_value QUEUE_CONNECTION "$QUEUE_CONNECTION"
set_env_value SESSION_DRIVER "$SESSION_DRIVER"
set_env_value MAIL_MAILER "$MAIL_MAILER"
set_env_value SANCTUM_STATEFUL_DOMAINS "$SANCTUM_STATEFUL_DOMAINS"
set_env_value SESSION_DOMAIN "$SESSION_DOMAIN"
set_env_value CORS_ALLOWED_ORIGINS "$CORS_ALLOWED_ORIGINS"

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

rm -f bootstrap/cache/*.php

php artisan package:discover --ansi >/dev/null

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec "$@"
