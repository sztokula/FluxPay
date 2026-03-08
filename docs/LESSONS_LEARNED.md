# Lessons Learned

## 1. Payment UX has to match system state
- A nice spinner is not enough if the worker is down.
- Showing retry windows and queue status removed most of the "is this stuck?" uncertainty.

## 2. Observability saves time every single day
- Queue heartbeat, failed jobs, and event logs made debugging much faster.
- The health endpoint became useful not only in monitoring, but also in manual checks during development.

## 3. Retries work only with idempotency plus tracing
- `Idempotency-Key` prevented duplicate writes when requests were retried.
- `X-Correlation-ID` let me follow one flow across API, jobs, and logs without guessing.

## 4. Runtime config should affect real behavior
- Settings like retry cap, fraud threshold, and watchdog toggle changed execution paths, not just labels in the UI.
- That made local testing closer to real production operations.

## 5. Small UI polish changes trust quickly
- Better empty states and clearer badges reduced support-style questions immediately.
- API console presets lowered copy/paste mistakes during manual API tests.

## 6. Refactors can silently break tests
- Job constructor changes broke tests that instantiated handlers directly.
- Keeping dependencies container-friendly and backward-safe helped avoid fragile test setups.

## 7. Good docs are part of delivery quality
- README, OpenAPI, changelog, and focused project notes made onboarding much smoother.
- Clear writing improved the perceived maturity of the project as much as many code-level tweaks.

## 8. "Mid-level" quality comes from end-to-end completeness
- The strongest signal was not one feature, but the full chain:
  - auth and authorization,
  - async processing,
  - retry and recovery paths,
  - observability,
  - CI and repeatable checks.
