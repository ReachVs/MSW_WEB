# API

Base URL: `http://localhost:8080`

## Current Status

The React client is not integrated with the backend API yet. For now:

- Admin UI is built with Laravel Blade at `/admin`.
- Client UI is built with React and uses local/static UI data.
- API routes may exist in Laravel, but they are not the current client integration target.

Use this file to document future API contracts before connecting React to Laravel.

## Health

`GET /api/health`

Returns:

```json
{"status":"ok"}
```

## Authentication

Sanctum session authentication is planned for future client/API integration.

- `GET /sanctum/csrf-cookie`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

## Bookings

Planned authenticated routes:

- `GET /api/bookings`
- `POST /api/bookings`
