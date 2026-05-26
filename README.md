# MSW Motorcycle Service Platform

Local full-stack application with a Laravel API, React frontend, and Docker runtime.

## Structure

- `backend/` Laravel API, models, requests, resources, tests, and application layers.
- `frontend/` React/Vite app organized by app, services, pages, components, features, hooks, layouts, routes, utils, and assets.
- `docker/` Container build and service configuration.
- `docs/` API, deployment, database, and Postman documentation.

## Local Runtime

```sh
cd docker
docker compose up --build
```

Or from the repository root:

```sh
docker compose up --build
```

Frontend: http://localhost:5173

Backend: http://localhost:8080

Mailpit: http://localhost:8025

Admin login: `admin@example.com` / `password`
