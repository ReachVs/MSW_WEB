# MSW Motorcycle Workshop Booking System

MSW is a full-stack motorcycle workshop booking and operations system with:

- `frontend/` React + Vite customer web app
- `backend/` Laravel admin dashboard (Blade) + mechanic portal + REST API
- MySQL database
- Docker Compose for local development

This repository reflects a production-like workflow:

- customers choose services, add motorcycle info, pick schedule slots, and submit bookings
- admins manage the workshop queue, calendar, mechanics, and walk-in bookings
- mechanics use a restricted backend portal that stays in sync with admin operations
- all bookings and services persist in the backend database

## Quick Links

All local links are also saved in [LOCAL_LINKS.md](file:///Users/ceaser/Documents/Work/Ongoing-Project/MSW/my-project/LOCAL_LINKS.md).

- Frontend: http://localhost:5173
- Admin: http://localhost:8080/admin
- Mechanic: http://localhost:8080/mechanic
- API: http://localhost:8080/api
- Health: http://localhost:8080/api/health

## Start With Docker

From the repository root:

First start on a new device, or after dependency changes:

```sh
docker compose up --build
```

Normal start:

```sh
docker compose up -d
```

Stop:

```sh
docker compose down
```

Docker services:

```text
backend
frontend
mysql
```

The backend container creates `backend/.env` from `backend/.env.example` if missing, installs Composer dependencies into a Docker volume, runs migrations, and runs seeders. The frontend container installs Node dependencies into a Docker volume.

## Admin Login

Seeded admin account:

```text
admin@example.com
password
```

## Customer App (Frontend)

Main screens:

- Catalog: hierarchical services with totals and remove actions
- My Garage: customer bookings (active) and add-new-service flow
- Service Archive: completed/cancelled history with remove-from-archive action
- Profile Information: editable profile stored locally and used for booking name

Booking flow:

1. Select services (4 main categories)
2. Review totals and remove unwanted items
3. Add motorcycle info (brand/name, model, plate number, engine capacity)
4. Choose schedule slot (calendar + time slots)
5. Submit booking (stored in backend, appears in My Garage and admin queue)

Service history behavior:

- service archive loads real backend history (`completed` and `cancelled`)
- customers can remove archive records (delete is restricted to archived bookings)

## Admin Dashboard (Backend)

Main screens:

- Queue: operational workflow sections with status tags and customer/motorcycle details
- Calendar: monthly overview with per-day booking counters and click-day booking list
- Mechanics: admin mechanic management and assignment workflow
- Add New Entry: walk-in booking flow (supports multiple services)

## Mechanic Portal

Mechanic portal location:

- `http://localhost:8080/mechanic`

Portal behavior:

- uses the backend Blade layout with a restricted mechanic-facing navigation
- can view queue, calendar, mechanics, and workshop status pages
- cannot access inventory management
- calendar capacity settings stay admin-only
- queue service archive is view-only for mechanic users
- shares queue and workshop data with admin views

Booking types:

- Booking: customer-submitted online bookings
- Walk In: admin-created bookings

The booking type tag shows in admin queue/dashboard (not in customer app).

Queue workflow sections:

- Pending
- Confirmed
- Repair
- Waiting Part
- Ready Pickup
- Service Archive (Completed / Cancelled)

Queue sync behavior:

- admin queue and mechanic queue use shared data and sync routes
- same-browser admin/mechanic tabs now notify each other immediately after queue form submissions
- polling remains as a fallback to catch background or cross-session updates
- queue pages auto-refresh only when the queue signature changes

Useful queue pages:

- Admin Queue: `http://localhost:8080/admin/queue`
- Mechanic Queue: `http://localhost:8080/mechanic/queue`

Status color system:

```text
Pending        #6B7280
Confirmed      #2563EB
Repair         #F59E0B
Waiting Part   #8B5CF6
Ready Pickup   #10B981
Completed      #059669
Cancelled      #EF4444
```

## API Summary

Auth (Sanctum):

- POST `/api/auth/login`
- POST `/api/auth/register`
- GET `/api/auth/me`
- POST `/api/auth/logout`

Catalog:

- GET `/api/services`

Customer bookings:

- GET `/api/bookings/active`
- GET `/api/bookings/history`
- POST `/api/bookings`
- PUT `/api/bookings/{id}/cancel`
- DELETE `/api/bookings/{id}` (only archived bookings: completed/cancelled)

Calendar:

- GET `/api/calendar/available-slots?date=YYYY-MM-DD`
- GET `/api/calendar/month?month=YYYY-MM`

Admin API:

- GET `/api/admin/bookings`
- GET `/api/admin/bookings/{id}`
- PUT `/api/admin/bookings/{id}/status`
- DELETE `/api/admin/bookings/{id}`
- GET `/api/admin/calendar`
- GET `/api/admin/calendar/day/{date}`

## Development Commands

Run checks via Docker:

Frontend:

```sh
docker compose exec frontend npm run format:check
docker compose exec frontend npm run lint
docker compose exec frontend npm run build
```

Backend:

```sh
docker compose exec backend ./vendor/bin/pint --test
docker compose exec backend php artisan test
```

Local non-Docker commands:

Frontend:

```sh
cd frontend
npm install
npm run dev
```

Backend:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8080
```

## CI Notes

CI enforces:

- Frontend formatting via Prettier (`npm run format:check`)
- Frontend lint via ESLint (`npm run lint`)
- Backend formatting via Pint (`./vendor/bin/pint --test`)
- Backend tests (`php artisan test`)

If CI fails on formatting, run:

```sh
docker compose exec frontend npm run format
docker compose exec backend ./vendor/bin/pint
```

## Common Troubleshooting

NPM error in repo root:

- If you see `Could not read package.json`, you ran npm in the wrong folder.
- Run frontend commands inside `frontend/`.

```sh
cd frontend
npm install
npm run build
```

## Repo Layout

```text
backend/     Laravel app (Blade admin, API, database, seeders)
frontend/    React/Vite customer app
docker/      Docker compose definitions
```
