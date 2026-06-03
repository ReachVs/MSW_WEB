# Docker Runtime

Run the full local stack from this Docker folder:

```sh
cd /Users/ceaser/Documents/Work/Ongoing-Project/MSW/my-project/docker
docker compose up --build
```

Services:

- Frontend: http://localhost:5173
- Backend API: http://localhost:8080
- Mailpit: http://localhost:8025
- MySQL 8: localhost:3306
- Redis: localhost:6379

If any port is already busy, copy `.env.example` to `.env` inside this folder and change the forwarded port values.

The backend container runs migrations and the idempotent seeder on startup. Use `admin@example.com` with password `password` for the seeded account.
