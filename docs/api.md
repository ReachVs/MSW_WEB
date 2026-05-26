# API

Base URL: `http://localhost:8080`

## Health

`GET /api/health`

Returns:

```json
{"status":"ok"}
```

## Authentication

Sanctum session authentication is used by the frontend.

- `GET /sanctum/csrf-cookie`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

## Bookings

Authenticated routes:

- `GET /api/bookings`
- `POST /api/bookings`
