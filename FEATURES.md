# TEMPLATE FEATURES

## Core Setup

- [X] Laravel 12 + Inertia SPA
- [X] Jetstream + Fortify + Sanctum auth flow
- [X] Roles and permissions via Spatie permission
- [X] Notifications page and API endpoints
- [X] Font Awesome and Lucide icon usage

## UI Strategy

- [X] daisyUI + Tailwind as the default UI layer
- [X] PrimeVue available for complex widgets only (DataTable, Autocomplete, MultiSelect)
- [ ] Expand reusable lightweight state components (empty/loading/toast)

## Backend Conventions (Phase 1)

- [X] Centralized pagination defaults (`app.pagination.default`, `app.pagination.max`)
- [X] Named route limiters (`notifications`, `user-actions`)
- [X] Conservative throttling applied to notifications and sensitive user actions

## Developer Experience (Phase 1)

- [X] Minimal quality scripts (`composer lint`, `composer format`, `composer test`)
- [X] Minimal frontend check scripts (`npm run check`, `npm run lint`, `npm run format`)
- [ ] Add targeted tests for all custom business flows
