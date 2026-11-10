# Deploy OpenApel on Coolify

Step-by-step for an auto-hosted Coolify on **Debian 13**. Coolify Traefik handles **HTTPS / Let’s Encrypt**. The app container speaks **HTTP only**.

Local **DDEV / Symfony CLI** workflows are untouched: this guide is production-only (`Dockerfile`, Coolify env). Dev keeps using `.env` / `.env.local` as today.

Companion checklist (no secrets): [`.env.prod.example`](./.env.prod.example).

---

## Why Dockerfile (not Compose) as the primary mode

| Mode | Use when |
| --- | --- |
| **Dockerfile (recommended)** | One app per school + Coolify-managed **PostgreSQL 18**. Coolify builds the image, injects env, mounts a volume, routes Traefik → container `:8080`. |
| **Docker Compose** | You want the repo `compose.yaml` smoke layout (app + Postgres) under Coolify Compose. Heavier to operate; DB backups/UI are better with Coolify’s Postgres resource. |

`compose.yaml` stays in the repo for local smoke tests and as a Compose fallback. Prefer **Dockerfile + separate Postgres**.

---

## Architecture

```
Internet → Coolify Traefik (TLS) → FrankenPHP :8080 (www-data, worker mode)
                                      ↓
                               PostgreSQL 18 (Coolify resource)
                                      ↓
                         volume → /app/var/storage/{APP_INSTANCE}/…
```

- Runtime: official `dunglas/frankenphp` **PHP 8.4** — **no PHP-FPM**
- Worker mode: `FRANKENPHP_CONFIG=worker ./public/index.php` (Symfony 7.4 native)
- Migrations: entrypoint runs `doctrine:migrations:migrate --no-interaction --all-or-nothing` (idempotent)

---

## 1. Create PostgreSQL 18

Coolify → **New resource** → **PostgreSQL** → version **18**.

One database **per school instance**. Note host, port, database, user, password (or use Coolify “Connect”).

`DATABASE_URL` shape:

```dotenv
DATABASE_URL="postgresql://USER:PASSWORD@HOST:5432/DBNAME?serverVersion=18&charset=utf8"
```

---

## 2. Create the application

1. **New resource** → **Application** → GitHub `ssepheriades/OpenApel`
2. Build pack: **Dockerfile**
3. Base directory: `/`
4. Dockerfile location: `Dockerfile`
5. Branch: `main` (after merge) or the release branch for a first smoke deploy
6. **Ports Exposes:** `8080` (non-root; not 80)
7. Domain: your hostname — enable HTTPS (Traefik / Let’s Encrypt)

---

## 3. Persistent volume (uploads)

Add a **named volume** (persistent storage):

| Coolify destination (container path) | Purpose |
| --- | --- |
| `/app/var/storage` | Photos, branding, documents (`UPLOAD_DIR`) |

On disk: `{UPLOAD_DIR}/{APP_INSTANCE}/{photos\|branding\|documents}/`.

**Permissions:** the entrypoint starts as root long enough to `chown` the volume, then **`gosu www-data`** (UID/GID **33**) before FrankenPHP. Leave Coolify’s container user at default root (or `0`) so that chown works; the web process itself is never root.

---

## 4. Environment variables

Paste from [`.env.prod.example`](./.env.prod.example). Minimum:

| Variable | Example |
| --- | --- |
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |
| `APP_SECRET` | `openssl rand -hex 32` |
| `APP_INSTANCE` | `tocqueville` (lowercase slug) |
| `UPLOAD_DIR` | `/app/var/storage` |
| `DATABASE_URL` | see §1 (`serverVersion=18`) |
| `DEFAULT_URI` | `https://asso.example.org` |
| `MAILER_DSN` | real SMTP DSN |
| `TRUSTED_PROXIES` | `REMOTE_ADDR` |
| `SERVER_NAME` | `:8080` |

Trusted **headers** are configured in `config/packages/framework.yaml` (`x-forwarded-*`). No `TRUSTED_HEADERS` env var is required.

Mark secrets as **runtime-only** (not available at build) when Coolify offers the toggle.

**Do not** set Coolify-managed TLS vars on the app (`ACME_EMAIL`, custom registry tags, etc.) unless you know you need them — Traefik owns certificates.

---

## 5. Deploy

1. Deploy / rebuild.
2. Watch logs: “Database is ready” → migrations → “OpenApel ready” → FrankenPHP.
3. Check `https://your.domain/health` → `{"status":"ok","database":"up"}`.

---

## 6. Post-deploy

```bash
# Coolify → Terminal / Exec on the app container
php bin/console app:create-admin-user
```

Then in `/admin` → **Réglages du site** (name, logo, colours, school year).

Smoke: publish a page, upload a photo, redeploy, confirm the file is still under `/app/var/storage/{APP_INSTANCE}/photos/`.

---

## 7. Messenger workers & cron

OpenApel **does not currently install** `symfony/messenger` or a scheduler — there is **no worker / cron to run today**.

When you add async messages or scheduled commands later:

### Option A — Coolify Process / Sidecar (recommended)

1. Same image as the web app.
2. Override start command, e.g.:
   - Worker: `php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M`
   - Scheduler (if using Symfony Scheduler / a cron command): `php bin/console messenger:consume scheduler_default` **or** a Coolify **Scheduled Task** calling `php bin/console app:your-cron`
3. Same env + `DATABASE_URL` as the web app (no need for the upload volume unless the job writes media).

### Option B — Host cron via Coolify Scheduled Task

```text
php bin/console app:your-command -vv
```

Schedule in Coolify UI (e.g. every 5 minutes). Keep commands idempotent.

### Option C — Single container supervisor

Only if you deliberately add a process manager to the image — avoid unless you accept a fatter image. Prefer A/B with FrankenPHP remaining the sole web process.

---

## 8. Multi-instance

Each school = **new** Coolify application + **new** Postgres 18 + **new** volume + unique `APP_INSTANCE` + domain. Never share DB or upload volumes.

---

## 9. Local smoke (optional, does not affect DDEV)

```bash
docker compose up --build
curl -fsS http://127.0.0.1:8080/health
```

Uses Postgres 18 from `compose.yaml` (volume mount `/var/lib/postgresql` for PG 18+). Stop with `docker compose down`.

Compose reads optional `OPENAPEL_*` overrides so it does not accidentally reuse Symfony `.env` keys (`APP_INSTANCE`, etc.) from the project root.

---

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Build fails on `npm run build` | Node stage logs; lockfile committed |
| Container exit on migrate | `DATABASE_URL`, Postgres 18 reachable from app network |
| 502 from Traefik | Ports Exposes = **8080**, not 80 |
| HTTPS redirect / wrong scheme | `TRUSTED_PROXIES=REMOTE_ADDR`, `DEFAULT_URI=https://…` |
| Upload permission denied | Volume writable by UID 33, or start as user `0` once |
| `/health` 503 | DB down or migrations not applied |
