# syntax=docker/dockerfile:1
#
# Coolify / production image for OpenApel (Symfony 7.4 + Vue SPA).
# TLS terminates at Coolify's Traefik; FrankenPHP listens on :80.

ARG PHP_VERSION=8.3
ARG NODE_VERSION=22

# -----------------------------------------------------------------------------
# Frontend build (Vite → public/build)
# -----------------------------------------------------------------------------
FROM node:${NODE_VERSION}-bookworm AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY assets ./assets
COPY vite.config.ts tsconfig.json ./
COPY templates ./templates

# Public API is same-origin behind Coolify; bake the relative base into the SPA.
ENV VITE_API_URL=/api
RUN npm run build

# -----------------------------------------------------------------------------
# PHP / FrankenPHP runtime
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php${PHP_VERSION} AS app

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl file \
    && install-php-extensions \
        @composer \
        apcu \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod \
    APP_DEBUG=0 \
    # Coolify / Traefik in front: HTTP only inside the container
    SERVER_NAME=:80 \
    FRANKENPHP_CONFIG="worker ./public/index.php"

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/app.ini $PHP_INI_DIR/conf.d/99-openapel.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint

# Composer deps first for layer caching
COPY composer.json composer.lock symfony.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

# Application source (frontend artefacts come from the node stage)
COPY bin bin
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY templates templates
COPY translations translations
COPY --from=frontend /app/public/build public/build

RUN composer dump-autoload --classmap-authoritative --no-dev \
    && mkdir -p var/cache var/log var/storage \
    && chmod +x bin/console \
    && chown -R www-data:www-data var

# Warmup needs a dummy secret; runtime overrides via Coolify env.
# DATABASE_URL is not required for cache:warmup when no DB calls run at compile time.
ENV APP_SECRET=build_time_placeholder_change_me \
    APP_INSTANCE=build \
    UPLOAD_DIR=/app/var/storage \
    DEFAULT_URI=http://localhost \
    DATABASE_URL="postgresql://app:app@127.0.0.1:5432/app?serverVersion=17&charset=utf8" \
    MAILER_DSN=null://null \
    TRUSTED_PROXIES=REMOTE_ADDR

RUN php bin/console cache:warmup --env=prod --no-debug \
    && chown -R www-data:www-data var

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/health || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
