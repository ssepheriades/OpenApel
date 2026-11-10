# Coolify deployment (OpenApel)

One Coolify application = one school instance (`APP_INSTANCE`). TLS is terminated by Coolify’s Traefik; the container listens on HTTP `:80`.

## Create in Coolify

1. **PostgreSQL 17** resource (dedicated DB per school).
2. **Application** from GitHub `ssepheriades/OpenApel`, build pack **Dockerfile**, context `/`, file `Dockerfile`.
3. **Ports Exposes:** `80`
4. **Domain:** your public hostname (HTTPS on).
5. **Persistent storage:** destination `/app/var/storage` (uploads: photos, branding, documents).
6. Wire `DATABASE_URL` to the Postgres resource (Coolify “Connect” / magic env).

Branch tip: deploy from `main` after merging the Coolify PR, or temporarily from the release branch for a first smoke deploy.

## Required environment variables

| Variable | Example / notes |
| --- | --- |
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |
| `APP_SECRET` | `openssl rand -hex 32` (Coolify secret) |
| `APP_INSTANCE` | lowercase slug, e.g. `tocqueville` |
| `UPLOAD_DIR` | `/app/var/storage` |
| `DATABASE_URL` | `postgresql://USER:PASS@HOST:5432/DB?serverVersion=17&charset=utf8` |
| `DEFAULT_URI` | `https://your.domain` (URL generation / mail links) |
| `MAILER_DSN` | real DSN in prod (`null://null` only for smoke) |
| `TRUSTED_PROXIES` | `REMOTE_ADDR` (default; Coolify Docker peer) |
| `SERVER_NAME` | `:80` |
| `VITE_API_URL` | `/api` (already baked at image build; runtime unused by SPA) |

Optional legacy placeholders in `.env` (`APP_DOMAIN`, `REGISTRY`, `IMAGE_TAG`, `ACME_EMAIL`) are **not** required for Coolify (registry/ACME are Coolify’s job).

Mark secrets (`APP_SECRET`, DB password, mailer credentials) as **runtime-only** where Coolify allows, so they are not available at image build.

## Build / start

- **Build:** Docker multi-stage (`npm run build` → Composer `--no-dev` → `cache:warmup`).
- **Start command:** image default — `frankenphp run --config /etc/caddy/Caddyfile`.
- **Entrypoint:** waits for DB, then `doctrine:migrations:migrate --no-interaction --all-or-nothing`.

## Post-deploy

```bash
# Inside the running container (Coolify terminal / exec)
php bin/console app:create-admin-user
```

Then set identity under **Réglages du site** (name, logo, colours, school year).

## Healthcheck

- Path: `GET /health` → `{"status":"ok","database":"up"}` (HTTP 200) or 503 if DB is down.
- Dockerfile `HEALTHCHECK` curls `http://127.0.0.1/health`.

## Multi-instance

Repeat the Coolify application + Postgres + volume per school. Never share `APP_INSTANCE` folders or databases across schools.

## Compose alternative

`compose.yaml` is a reference for Coolify **Docker Compose** mode (app only + upload volume). Prefer Dockerfile + managed Postgres for day-to-day ops.
