# syntax=docker/dockerfile:1
#
# Production image for Coolify (Traefik TLS in front).
# - dunglas/frankenphp + PHP 8.4 (no PHP-FPM)
# - FrankenPHP worker mode (Symfony 7.4 native)
# - Non-root (www-data), HTTP on :8080
# Local DDEV / Symfony CLI are unchanged — this file is prod-only.

ARG NODE_VERSION=22

# -----------------------------------------------------------------------------
# Stage 1 — Vite SPA / admin assets
# -----------------------------------------------------------------------------
FROM node:${NODE_VERSION}-bookworm AS frontend

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY assets ./assets
COPY vite.config.ts tsconfig.json ./
COPY templates ./templates

ENV VITE_API_URL=/api
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 2 — Composer vendor (no scripts / no autoload dump yet)
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS vendor

WORKDIR /app

RUN install-php-extensions \
        @composer \
        apcu \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        zip

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

# -----------------------------------------------------------------------------
# Stage 3 — Runtime (lean, non-root)
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS app

WORKDIR /app

# curl = HEALTHCHECK; gosu = root→www-data drop when Coolify starts as root
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl gosu file \
    && install-php-extensions \
        apcu \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/* \
    # Non-privileged port only — drop NET_BIND_SERVICE capability
    && setcap -r /usr/local/bin/frankenphp \
    && mkdir -p /data/caddy /config/caddy /app/var/cache /app/var/log /app/var/storage \
    && chown -R www-data:www-data /data/caddy /config/caddy /app/var

COPY docker/php/app.ini $PHP_INI_DIR/conf.d/99-openapel.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod \
    APP_DEBUG=0 \
    SERVER_NAME=:8080 \
    FRANKENPHP_CONFIG="worker ./public/index.php" \
    UPLOAD_DIR=/app/var/storage \
    TRUSTED_PROXIES=REMOTE_ADDR

# Vendor from stage 2 + application source
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json /app/composer.lock /app/symfony.lock ./

# Committed defaults only (no secrets). Runtime env from Coolify overrides these.
COPY .env ./
COPY bin bin
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY templates templates
COPY translations translations
COPY --from=frontend /build/public/build public/build

# Build-time placeholders only (overridden at runtime by Coolify). Never bake real secrets.
ENV APP_SECRET=build_time_placeholder_change_me \
    APP_INSTANCE=build \
    DEFAULT_URI=http://localhost \
    DATABASE_URL="postgresql://app:app@127.0.0.1:5432/app?serverVersion=18&charset=utf8" \
    MAILER_DSN=null://null

RUN composer dump-autoload --classmap-authoritative --no-dev \
    && chmod +x bin/console \
    && php bin/console cache:warmup --env=prod --no-debug \
    && chown -R www-data:www-data /app \
    && rm -f /usr/local/bin/composer

# Container may start as root so the entrypoint can chown Coolify named volumes,
# then always drops to www-data via gosu before FrankenPHP (non-root process).
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1:8080/health || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
