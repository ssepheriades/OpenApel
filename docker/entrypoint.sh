#!/bin/sh
set -eu

if [ "${1:-}" = 'frankenphp' ] || [ "${1:-}" = 'php' ] || [ "${1:-}" = 'bin/console' ]; then
    mkdir -p /app/var/cache /app/var/log /app/var/storage
    # Persist uploads across deploys when Coolify mounts /app/var/storage.
    chown -R www-data:www-data /app/var 2>/dev/null || true

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

        if find ./migrations -iname '*.php' -print -quit 2>/dev/null | grep -q .; then
            php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
        fi
    fi

    echo 'OpenApel ready.'
fi

exec docker-php-entrypoint "$@"
