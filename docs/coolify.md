# Coolify (short pointer)

Full production runbook: **[`DEPLOY_COOLIFY.md`](../DEPLOY_COOLIFY.md)**.

Env template (no secrets): [`.env.prod.example`](../.env.prod.example).

Highlights:

- FrankenPHP PHP **8.4**, worker mode, HTTP **`:8080`**, Traefik TLS
- Coolify **PostgreSQL 18** + volume `/app/var/storage`
- Dockerfile build pack (Compose only for smoke / fallback)
- Local DDEV unchanged
