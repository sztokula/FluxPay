# Lessons Learned

## 1. Payment UX must mirror backend reality
- A polished loading state is not enough if queue workers are not running.
- Exposing retry timing and system health prevented "infinite processing" confusion.

## 2. Operational visibility is a feature, not an afterthought
- Dashboard-level observability (queue, failures, heartbeats) significantly reduced debugging time.
- API health endpoints are useful for both humans and automation.

## 3. Safe retries require both idempotency and traceability
- `Idempotency-Key` solved duplicate writes.
- `X-Correlation-ID` made end-to-end troubleshooting practical.

## 4. Configurable runtime behavior improves operational realism
- Adding settings that affect real logic (retry cap, fraud threshold, watchdog toggle) made the project look and behave like a production system.

## 5. Small UX improvements create big trust gains
- Better empty states, clear status badges, and explicit next steps improved perceived quality quickly.
- API console presets reduced mistakes during manual testing.

## 6. Tests break when architecture evolves unless backward compatibility is considered
- Injecting new dependencies into existing jobs can break tests that call handlers directly.
- Optional dependency parameters or container resolution patterns help preserve stability.

## 7. Documentation quality improves project adoption
- Case-study README, OpenAPI spec, deployment docs, and a changelog improved onboarding and maintenance.
- Recruiters and hiring managers evaluate communication quality almost as much as code quality.

## 8. Mid-level signal comes from completeness
- Not just CRUD and UI, but also:
  - auth and authorization,
  - async processing,
  - recovery paths,
  - observability,
  - CI.
