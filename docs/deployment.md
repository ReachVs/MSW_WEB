# Deployment

The local stack is defined in `docker/compose.yaml`.

Root-level compose files are provided for convenience:

- `docker-compose.yml` includes the local Docker stack.
- `docker-compose.prod.yml` contains production-oriented restart overrides and image references.

Review environment variables before deploying:

- `APP_URL`
- `FRONTEND_HTTP_PORT`
- `BACKEND_HTTP_PORT`
- `SANCTUM_STATEFUL_DOMAINS`
- `CORS_ALLOWED_ORIGINS`
- database and Redis credentials
