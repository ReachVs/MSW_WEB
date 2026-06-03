# Architecture

## Current Boundary

This project is split into two UI applications that share one repository.

### Laravel Admin

The admin dashboard is built with Laravel Blade in `backend/resources/views/admin`.

Admin responsibilities:

- Dashboard overview
- Booking management
- Work order management
- Customer records
- Inventory management
- Reports
- Settings
- Staff/admin workflows

The admin dashboard should use Laravel routes, Blade views, policies, form requests, service classes, and database models.

### React Client

The client/customer side is built with React in `frontend/src`.

Client responsibilities:

- Landing page
- Services page
- Public booking flow UI
- Customer-facing screens

The React app currently uses local mock data only. It should not call the backend API until the UI flow and process flow are approved.

The two UI surfaces should stay separate:

- Laravel Blade owns the admin dashboard.
- React owns the public/client side.
- API integration waits until the admin and client flows are approved.

## Future API Integration

When the team is ready to integrate APIs:

1. Finalize the React customer flow.
2. Define API contracts in `docs/api.md`.
3. Build Laravel endpoints with request validation and feature tests.
4. Replace React mock services with real API services.
5. Keep admin Blade workflows server-rendered unless there is a strong reason to add JavaScript.

## Recommended Backend Layers

Use these only when the feature needs them:

- `app/Http/Controllers` for request entry points.
- `app/Http/Requests` for validation.
- `app/Models` for database entities.
- `app/Services` for business workflows.
- `app/Actions` for small single-purpose operations.
- `app/Policies` for authorization.
- `app/Notifications` for email and queued notifications.
- `database/seeders` for demo and default data.

Avoid creating empty folders just to suggest architecture. Add folders when a real feature needs them.
