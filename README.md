# MSW Motorcycle Service Platform

MSW is a motorcycle service management project with two intentionally separated UI surfaces:

- `backend/` Laravel application for the admin dashboard, backend models, database, queues, email, and future APIs.
- `frontend/` React/Vite application for the public client/customer side.

Current development rule: **admin UI is Laravel Blade, client UI is React, and the React client is not integrated with the API yet.**

## Start With Docker

From the repository root:

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

Docker starts:

- Laravel/PHP backend
- React/Vite frontend
- MySQL

Open:

- React client UI: http://localhost:5173
- Laravel admin UI: http://localhost:8080/admin
- Backend health check: http://localhost:8080/api/health

The backend container creates `backend/.env` from `backend/.env.example` if it is missing, installs Composer dependencies into the Docker `backend-vendor` volume, runs migrations, and runs seeders.

The frontend container installs Node dependencies from `frontend/package-lock.json` into the Docker `frontend-node-modules` volume. Team members do not need to run `npm install` or `composer install` manually to start the project.

Do not manually delete `backend/vendor` or `frontend/node_modules` while the containers are running. Those paths are Docker dependency mounts. If dependencies are missing or broken, rebuild the services:

```sh
docker compose up -d --build
```

Seeded admin account:

```text
admin@example.com
password
```

## Development Commands

Use Docker commands for development tasks.

Frontend:

```sh
docker compose exec frontend npm run build
docker compose exec frontend npm run lint
```

Backend:

```sh
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec backend php artisan test
```

Current Docker services:

```text
backend
frontend
mysql
```

Redis, queue worker, scheduler, Nginx, and Mailpit are intentionally not running yet. Add them back only when the project needs real async jobs, email testing, or a production-like web server.

## Architecture Direction

Admin dashboard:

- Built with Laravel Blade.
- Lives in `backend/resources/views/admin`.
- Starts at `/admin`.
- Will manage bookings, work orders, customers, inventory, reports, and settings.

Client side:

- Built with ReactJS.
- Lives in `frontend/src`.
- Uses static/mock data for now.
- Will later connect to backend APIs after the UI flow is approved.

Backend:

- Owns database schema, seeders, queues, notifications, auth, and future API endpoints.
- Current API code can stay, but it is not the client-side integration target yet.

## Team Rule

Do not mix admin and client UI responsibilities:

- Put admin screens in Laravel Blade.
- Put customer/public screens in React.
- Keep API integration out of React until the UI and process flow are finalized.
