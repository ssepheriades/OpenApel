#!/bin/sh
set -eu

# When the container is started as root (Coolify default unless overridden),
# fix upload volume ownership then re-exec as www-data. The image USER is
# www-data; this path only runs if the orchestrator overrides to root.
if [ "$(id -u)" = '0' ]; then
    mkdir -p /app/var/cache /app/var/log /app/var/storage
    chown -R www-data:www-data /app/var /data/caddy /config/caddy 2>/dev/null || true
    exec gosu www-data "$0" "$@"
fi

mkdir -p /app/var/cache /app/var/log /app/var/storage

# Ensure per-instance upload trees exist (photos / branding / documents).
if [ -n "${APP_INSTANCE:-}" ]; then
    for mapping in photos branding documents; do
        mkdir -p "${UPLOAD_DIR:-/app/var/storage}/${APP_INSTANCE}/${mapping}"
    done
fi

if [ "${1:-}" = 'frankenphp' ] || [ "${1:-}" = 'php' ] || [ "${1:-}" = 'bin/console' ]; then
    if [ -n "${DATABASE_URL:-}" ]; then
        echo 'Waiting for database to be ready...'
        attempts=60
        until php bin/console dbal:run-sql -q "SELECT 1" >/dev/null 2>&1; do
            attempts=$((attempts - 1))
            if [ "$attempts" -le 0 ]; then
                echo 'Database is not reachable (DATABASE_URL).' >&2
                exit 1
            fi
            sleep 1
        done
        echo 'Database is ready.'

        # Idempotent: Doctrine tracks applied versions; safe on every deploy.
        if find ./migrations -iname '*.php' -print -quit 2>/dev/null | grep -q .; then
            php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
        fi
    fi

    echo 'OpenApel ready.'
fi

exec docker-php-entrypoint "$@"
