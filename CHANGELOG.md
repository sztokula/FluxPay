# Changelog

All notable changes to this project are documented in this file.

## [4.2.1] - 2026-03-07
### Fixed
- Documentation pages now render with production-like layout: left content area and right sticky section navigation.
- Heading anchors are generated automatically for markdown docs, so TOC links always work.

### Improved
- Documentation readability for long files on desktop and mobile.

---

## [4.2.0] - 2026-03-07
### Added
- Public documentation hub routes:
  - `/documentation`
  - `/changelog`
  - `/what-i-learned`
- Shared docs page template with reusable action links between docs sections.

---

## [4.1.2] - 2026-03-07
### Fixed
- API reference CTA for authenticated users now shows logout action instead of redundant login link.

## [4.1.1] - 2026-03-07
### Fixed
- Payment processing polling now respects configured polling interval and avoids hardcoded timing drift.

## [4.1.0] - 2026-03-07
### Improved
- Payment waiting screen UX:
  - clearer long-processing guidance,
  - stronger recovery hints when queue worker is missing.

---

## [4.0.0] - 2026-03-07
### Added
- Documentation experience uplift:
  - discoverable doc links in top navigation and footer,
  - project learning narrative surfaced as first-class page.

---

## [3.4.2] - 2026-03-07
### Fixed
- Guest access redirects now consistently target login page for web protected routes.

## [3.4.1] - 2026-03-07
### Fixed
- Dashboard feature tests adjusted for authenticated route requirements.

## [3.4.0] - 2026-03-07
### Improved
- Login/register use intended redirect, returning users to the exact protected page they requested.

---

## [3.3.1] - 2026-03-07
### Fixed
- API token management tests aligned with authenticated user context.

## [3.3.0] - 2026-03-07
### Added
- Web auth protection for dashboard and API reference screens.

### Improved
- Navigation now adapts by auth state (`Login/Register` vs `API/Dashboard/Logout`).

---

## [3.2.1] - 2026-03-07
### Fixed
- Order finalization test flow now authenticates before opening protected event pages.

## [3.2.0] - 2026-03-07
### Added
- Regression tests for guest redirect behavior and protected docs access patterns.

---

## [3.1.1] - 2026-03-07
### Fixed
- Prevented stale UX hints in API page by aligning CTA and access model.

## [3.1.0] - 2026-03-07
### Added
- Portfolio-level docs pages linked directly from application chrome.

---

## [3.0.0] - 2026-03-07
### Added
- Portfolio documentation set:
  - case-study README,
  - deployment guide,
  - security/performance checklist,
  - lessons learned.

### Improved
- Portfolio-readiness posture with stronger narrative and delivery context.

---

## [2.6.1] - 2026-03-07
### Fixed
- API console request error handling and response display consistency.

## [2.6.0] - 2026-03-07
### Added
- CI workflow for PHP style checks, tests, and frontend asset build.

## [2.5.1] - 2026-03-07
### Fixed
- API unauthenticated responses standardized with correlation-id response field.

## [2.5.0] - 2026-03-07
### Added
- Correlation-id middleware for API traceability.
- API throttling with runtime-configurable rate limits.

## [2.4.1] - 2026-03-07
### Fixed
- Watchdog stale-processing handling under queued retry edge cases.

## [2.4.0] - 2026-03-07
### Added
- Processing watchdog command and scheduler integration.

## [2.3.0] - 2026-03-07
### Added
- System observability dashboard and API health endpoint (`/api/system/health`).

## [2.2.1] - 2026-03-07
### Fixed
- Empty-state behavior in dashboard tables for cleaner first-run experience.

## [2.2.0] - 2026-03-07
### Added
- API reference redesign:
  - token management,
  - interactive request console,
  - request presets.

## [2.1.0] - 2026-03-07
### Added
- Extended settings center:
  - retry cap,
  - fraud threshold,
  - API rate limit,
  - polling interval,
  - maintenance banner,
  - guest checkout toggle.

## [2.0.0] - 2026-03-07
### Added
- Full FluxPay dashboard modules:
  - payments, subscriptions, invoices, payouts,
  - events, customers, ledger, webhooks, fraud, plans, orders, system, settings.

---

## [1.7.0] - 2026-03-07
### Added
- Web authentication flow (register, login, logout) with dedicated pages and tests.

## [1.6.0] - 2026-03-07
### Improved
- Runtime behavior now responds to app settings in core payment and checkout flows.

## [1.5.0] - 2026-03-07
### Added
- Worker/scheduler heartbeat visibility and stale-queue diagnostics.

## [1.4.0] - 2026-03-07
### Added
- Local API developer experience page for quick onboarding.

## [1.3.0] - 2026-03-07
### Added
- Queue-backed retry flow and deterministic failure simulation behavior.

## [1.2.1] - 2026-03-06
### Fixed
- Payment status transition consistency for retryable failures.

## [1.2.0] - 2026-03-06
### Added
- Expanded dashboard visual modules and status badges.

## [1.1.1] - 2026-03-06
### Fixed
- Frontend fallback message when Vite assets are unavailable.

## [1.1.0] - 2026-03-06
### Added
- Storefront core flow:
  - product listing,
  - checkout,
  - payment page,
  - success/failure routes.

## [1.0.0] - 2026-03-06
### Added
- Initial domain foundation:
  - customers,
  - payment intents,
  - subscriptions,
  - invoices,
  - webhooks,
  - fraud,
  - ledger,
  - payouts,
  - event logs.

---

## Real Worklog (7 days, 4-6h/day)

### Day 1 (4h) - Core domain and schema
- Built initial models, enums, migrations, and seed baseline.
- Implemented foundational API resources and authorization policy skeletons.

### Day 2 (5h) - Storefront and checkout foundation
- Shipped product list/detail screens and checkout bootstrap.
- Added payment intent creation and confirmation entry points.

### Day 3 (6h) - Async processing and retries
- Added queue processing, retry policy, and webhook event publishing.
- Stabilized payment state transition logic for success/failure/action-required.

### Day 4 (4h) - Dashboard expansion
- Added broad dashboard modules with table views and key summaries.
- Improved status badges, empty states, and navigation clarity.

### Day 5 (5h) - API developer UX
- Built API reference page with token tools and request console.
- Added correlation-id handling and stricter auth response consistency.

### Day 6 (6h) - Operations and resilience
- Added observability dashboard and API health endpoint.
- Implemented processing watchdog and scheduler integration.

### Day 7 (5h) - Portfolio polish and docs
- Added auth flow hardening, CI workflow, OpenAPI artifacts, and docs pages.
- Completed documentation UX polish with section navigation and content hierarchy.
