# Security & Performance Checklist

## Security Controls Implemented

- API authentication via Sanctum bearer tokens.
- Authorization policies for API resources.
- Idempotency middleware for mutating API requests.
- API rate limiting (`throttle:api`) with configurable rpm.
- Correlation-id on API responses (`X-Correlation-ID`) for traceability.
- Request validation through FormRequest classes.

## Operational Safety

- Failed jobs table and dashboard visibility.
- Processing watchdog for stale `processing` intents.
- Scheduler + queue heartbeats exposed in system dashboard and API health.

## Performance Notes

- Pagination on dashboard/API list endpoints.
- Narrow table queries for operational counters.
- Retry policy with bounded attempts.

## Recommended Next Improvements

- Add Redis queue backend for higher throughput.
- Add DB indexes based on real traffic profile.
- Add Sentry/centralized log sink for production.
- Add load tests for API hotspots (`payment-intents`, `system/health`).
