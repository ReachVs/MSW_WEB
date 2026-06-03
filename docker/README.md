# Docker Runtime

Run the full local stack from the repository root.

First start on a new device, or after dependency changes:

```sh
docker compose up --build
```

Normal start after the first build:

```sh
docker compose up -d
```

Stop all services:

```sh
docker compose down
```

Services:

- React client: http://localhost:5173
- Laravel admin/backend: http://localhost:8080
- Laravel admin dashboard: http://localhost:8080/admin
- MySQL 8: localhost:3306

The backend container creates `backend/.env` from `backend/.env.example` when it is missing. It also installs Composer dependencies, runs migrations, and runs the idempotent seeder on startup.

The frontend container installs Node dependencies from `frontend/package-lock.json`. No manual `npm install` or `composer install` is required to start the project on a new device.

Seeded admin account:

```text
admin@example.com
password
```

If a port is already busy, copy `docker/.env.example` to `docker/.env` and change the forwarded port values.

Current Docker services:

```text
backend
frontend
mysql
```

Redis, queue worker, scheduler, Nginx, and Mailpit are intentionally disabled for the current UI-development stage.
