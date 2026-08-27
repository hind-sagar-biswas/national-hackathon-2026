# Admin Template Starter Kit

A Laravel 13 starter kit for admin-style products with built-in authentication flows, role and permission support, Inertia + Vue UI, and configurable feature flags.

## What This Template Includes

- Laravel 13 backend with Inertia.js v2 + Vue 3 frontend
- Jetstream + Fortify + Sanctum authentication stack
- Role and permission management with Spatie Permission
- Feature-flag-driven auth and admin behaviors
- Notifications page and authenticated notifications API
- Tailwind CSS v4 + daisyUI as the default UI layer
- PrimeVue available for complex components
- Pest test setup and Laravel Pint formatting workflow

## Tech Stack

### Backend

- PHP `^8.2`
- Laravel `^13.0`
- Inertia Laravel `^2.0`
- Jetstream `^5.4`
- Sanctum `^4.0`
- Spatie Permission `^6.24`

### Frontend

- Vue `^3.5`
- Inertia Vue `^2.3`
- Tailwind CSS `^4.2`
- daisyUI `^5.5`
- PrimeVue `^4.5`
- Vite `^7.3`

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 20+ and npm (or Bun as an alternative package manager)
- MySQL, PostgreSQL, or SQLite

## Quick Start

Use the project setup script for the fastest local bootstrap:

```bash
composer run setup
```

The setup script will:

- install PHP dependencies
- create `.env` if missing
- generate the app key
- run database migrations
- install frontend dependencies
- build frontend assets

Then run the development stack:

```bash
composer run dev
```

This starts:

- Laravel app server
- queue worker
- log tailing with Pail
- Vite dev server

## Manual Setup

If you prefer explicit steps:

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan wayfinder:generate --with-form --no-interaction
php artisan storage:link
npm run build
```

Then start development:

```bash
composer run quick
```

`composer run quick` starts only the app server and Vite.

## Default Seeded Admin Account

After running `php artisan migrate --seed`, a super admin account is created:

- Email: `super@test.com`
- Password: `password`

## Environment Configuration

The template uses environment flags to control product capabilities.

### Auth and account features

- `REGISTRATION=true`
- `RESET_PASSWORD=true`
- `EMAIL_VERIFICATION=true`
- `UPDATE_PROFILE_INFORMATION=true`
- `UPDATE_PASSWORDS=true`
- `MF_AUTHENTICATION=true`
- `ACC_DELETION=true`

### Jetstream feature flags

- `PROFILE_PIC=true`
- `API_SUPPORT=true`
- `TERMS_AND_COND=true`
- `TEAMS=false`

### App behavior flags

- `USER_BAN=false`
- `PAGINATION_SIZE=20`
- `HOME_ROUTE=/dashboard`
- `USER_NAME=email`

### Rate limit configuration

These are configured in `config/app.php`:

- `RATE_LIMIT_NOTIFICATIONS_PER_MINUTE` (default 60)
- `RATE_LIMIT_USER_ACTIONS_PER_MINUTE` (default 20)

## Routes and Access Model

### Web routes

- `GET /` public welcome page
- `GET /dashboard` authenticated and verified users
- `GET /notifications` authenticated users (throttle protected)
- `PATCH /users/{user}/toggle` available only when `USER_BAN=true` and user has the required permission

### API routes (Sanctum auth required)

- `GET /api/user`
- `GET /api/notifications`
- `POST /api/notifications/{id}/read`
- `POST /api/notifications/read-all`
- `DELETE /api/notifications/{id}`

## Authorization Model

Roles and permissions are defined with enums:

- Roles: `admin`, `user`
- Permissions: `view-users`, `create-users`, `update-users`, `delete-users`, `toggle-users`, `delete-account`

Permission assignment uses `App\Utils\RolePermissionMap`, which conditionally adds `toggle-users` for admins when `USER_BAN=true`.

## Frontend Notes

- Inertia Vue pages live in `resources/js/Pages`
- Shared UI components live in `resources/js/Components`
- Layouts live in `resources/js/Layouts`
- Tailwind and daisyUI config is in `resources/css/app.css`
- Typed route/action helpers are generated via Wayfinder

Regenerate typed route helpers after route/controller changes:

```bash
php artisan wayfinder:generate --with-form --no-interaction
```

## Common Commands

### Development

```bash
composer run dev
composer run quick
npm run dev
npm run build
```

### Tests

```bash
php artisan test
php artisan test --compact
php artisan test --filter=yourTestName
```

### Code quality

```bash
composer run lint
composer run format
vendor/bin/pint --dirty
vendor/bin/pint
```

## Project Structure

```text
app/
	Actions/        # Fortify and domain actions
	Enums/          # Role and Permission enums
	Http/
		Controllers/  # Web/API controllers
		Requests/     # Form requests
		Resources/    # API resources
	Models/         # Eloquent models
	Utils/          # Helpers and role/permission map
config/           # App, Fortify, Jetstream config
database/
	migrations/
	seeders/
resources/
	css/
	js/
routes/
tests/
```

## Troubleshooting

### Frontend changes do not appear

Run one of:

```bash
npm run dev
npm run build
composer run dev
```

### Login, registration, or profile actions missing

Check your `.env` feature flags and confirm corresponding toggles are enabled.

### Account toggle route not available

`PATCH /users/{user}/toggle` exists only when `USER_BAN=true`.

### Local queue jobs not processing

Use `composer run dev` so the queue listener runs with the app server.

## Additional Docs

- `FEATURES.md` for feature status tracking
- `AGENTS.md` for repository-specific assistant guidance

## License

MIT