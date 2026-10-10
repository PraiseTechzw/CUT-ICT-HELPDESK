# Testing Strategy

**Status:** Automated Phase 1 coverage is present and has run successfully. Ticketing, institutional database compatibility, browser-based end-to-end tests, security review, and UAT are not complete.

## Test setup and isolation

- PHPUnit 12.5 (Laravel's feature/unit stack) runs through `php artisan test`.
- `phpunit.xml` sets test-only environment values. The shared `Tests\TestCase::createApplication()` also overrides inherited shell environment variables **before Laravel boots**, forcing `APP_ENV=testing`, a test-only `APP_KEY`, array session/cache, sync queue, and SQLite `:memory:`. The base case asserts the test environment and database connection or fails closed.
- Feature tests use `RefreshDatabase`; identities and locations are synthetic. No production/staff/student data or privileged seed account is used.
- Frontend assets are built by Vite. Blade responses are exercised in feature tests; this is not a browser-driven accessibility or E2E audit.

## Implemented coverage

The final full run reported **27 tests passed (144 assertions)**, covering:

- Login page, valid and invalid credentials, inactive login/session denial, session ID rotation, logout invalidation and CSRF token regeneration, guest-route protection, and rate limiting.
- Requester denial for account/user and location administration, including direct edit URLs and writes.
- Account creation, normalized email, least-privilege default role, password hashing, safe audit metadata, protected-field mass-assignment resistance, role assignment/revocation, self-role/self-deactivation denial, account deactivate/reactivate audit, last-active-administrator safeguards, search, and administrator bootstrap command.
- Authorized location screens and hierarchy creation; campus/zone/building/room parent-child constraints; campus-scoped zone uniqueness; normalized codes; cross-campus-zone rejection; and foreign-key protection of referenced campuses.
- Guest root redirection and framework health endpoint.

## Actual checks performed

- `composer test` — **27 passed, 144 assertions** (the script clears config and runs the complete Laravel suite).
- `npm run build` — **passed** with Vite 8.3.4 after removing the remote font fetch.
- `./vendor/bin/pint --test` — **passed** for 65 PHP files.
- `composer validate --no-check-publish` — **valid** manifest and lock.
- PHP syntax lint — **66 files passed**.
- `composer audit --locked` — no security vulnerability advisories found.
- `npm audit --audit-level=moderate` — zero vulnerabilities found.

Commands and results describe this sandbox verification only; repeat quality checks after later edits. Do not infer that security review, MySQL/MariaDB migrations, CI, deployment, institutional UAT, or production readiness have been completed.

## Required later-phase tests

When those features are approved and implemented, add coverage for requester ownership, regional data isolation, scoped team/deployment authorization and effective-date boundaries, unknown-location triage, ticket state transitions and atomic history, private-note/attachment non-disclosure, assignment/escalation rules, notifications/retries, privacy retention, approved migration upgrade paths, and restore rehearsal. Inactive-location behavior in future ticket/workflow paths must be tested when those paths exist.

Before a pilot, add browser/keyboard/accessibility testing against CUT's approved standard, approved UAT, SQL-engine-specific migration tests, CI quality gates, security/privacy review, and recovery evidence. Meaningful boundary assertions matter more than a percentage-only coverage threshold.
