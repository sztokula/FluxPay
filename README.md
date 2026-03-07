# FluxPay (Laravel 12)

Production-inspired local payment platform focused on payment lifecycle, retries, webhook delivery, fraud rules, ledger records, and operational observability.

## Why This Project

This project demonstrates mid-level backend/fullstack skills:
- domain modeling for payment systems,
- async processing with queues and retries,
- idempotent API writes,
- API auth with Sanctum tokens,
- operational dashboards (queue, watchdog, health),
- automated test suite and CI workflow.

## Core Features

- Storefront with checkout and deterministic test cards.
- Dashboard pages: payments, subscriptions, invoices, payouts, events, ledger, webhooks, fraud, orders, system.
- API reference page with:
  - token management,
  - interactive API console,
  - correlation-id and idempotency headers.
- Processing watchdog for stale `processing` payment intents.
- Settings center (`Dashboard > Settings`) with live business/system toggles.

## Architecture Snapshot

- Framework: Laravel 12, PHP 8.4.
- DB: SQLite (local), queue tables, failed jobs.
- Auth: session auth (web) + Sanctum bearer auth (API).
- Async: `ProcessPaymentIntent` + retry jobs + scheduler.
- Observability:
  - `X-Correlation-ID` middleware,
  - event logs,
  - `/api/system/health`,
  - system dashboard heartbeats.

## Business Flow

1. User opens product and starts checkout.
2. App creates `PaymentIntent` in `requires_confirmation`.
3. User confirms card, intent moves to `processing`.
4. Background processor resolves to `succeeded`, `failed`, or `requires_action`.
5. On success: invoice/subscription/order updates, ledger entries, webhook events.
6. On retryable failure: retry scheduling based on retry policy.
7. Watchdog marks stale processing intents as failed when worker execution is missing.

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

For full live behavior:

```bash
php artisan queue:work
php artisan schedule:work
```

## Access

- Web auth:
  - register at `/register`, then login at `/login`.
- API:
  - open `/api-reference`,
  - create token,
  - use `Try API Console` or curl with `Authorization: Bearer ...`.

## API Spec

- OpenAPI file: [openapi/openapi.yaml](openapi/openapi.yaml)
- API reference UI: `/api-reference`

## Quality / Tooling

- Lint/style: `vendor/bin/pint --dirty`
- Tests: `php artisan test --compact`
- Frontend build: `npm run build`
- CI: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

## Deployment Notes

See:
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [docs/SECURITY_PERFORMANCE.md](docs/SECURITY_PERFORMANCE.md)
- [CHANGELOG.md](CHANGELOG.md)
- [docs/LESSONS_LEARNED.md](docs/LESSONS_LEARNED.md)
