# Database

The application uses MySQL in Docker.

Local connection:

- Host: `localhost`
- Port: `3307`
- Database: `laravel`
- User: `laravel`
- Password: `password`

Migrations live in `backend/database/migrations`.

Seed data is defined in `backend/database/seeders/DatabaseSeeder.php`.

Inside Docker, Laravel connects to MySQL with:

- Host: `mysql`
- Port: `3306`

The backend container runs migrations and seeders automatically on startup.
