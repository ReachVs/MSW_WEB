# Deployment

The local stack is defined in `docker/compose.yaml`.

Root-level compose files are provided for convenience:

- `docker-compose.yml` includes the local Docker stack.
- `docker-compose.prod.yml` contains production-oriented restart overrides and image references.

Review environment variables before deploying:

- `APP_URL`
- `FRONTEND_HTTP_PORT`
- `BACKEND_HTTP_PORT`
- `MYSQL_FORWARD_PORT`
- `SANCTUM_STATEFUL_DOMAINS`
- `CORS_ALLOWED_ORIGINS`
- database credentials

## Current Local Services

The development stack currently runs only:

- `backend`
- `frontend`
- `mysql`

Redis, queue worker, scheduler, Nginx, and Mailpit are intentionally not active yet.

## Dependency Volumes

Composer and Node dependencies are stored in Docker named volumes:

- `backend-vendor`
- `frontend-node-modules`

If dependencies are missing or stale, rebuild the stack from the repository root:

```sh
docker compose up -d --build
```
