# Deployment Guide

## Target Runtime

- PHP 8.4
- SQLite (or PostgreSQL if adapted)
- Queue worker required
- Scheduler required

## Minimal Steps

1. Install dependencies.
2. Configure `.env`.
3. Run migrations and seeders.
4. Build frontend assets.
5. Run app server, queue worker, scheduler.

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Long-Running Processes

```bash
php artisan queue:work --tries=1
php artisan schedule:work
```

## Health Checks

- HTTP: `/up`
- API: `/api/system/health` (bearer auth)
- UI: `/dashboard/system`

## Rollback

- Deploy previous build artifacts.
- Restore DB backup.
- `php artisan config:clear` if stale config cache.
