# Testing Strategy

**Status:** Phase 1 and the user-approved Phase 2 prototype have automated local coverage. Institutional database compatibility, browser-based end-to-end/accessibility tests, independent security review, and UAT are not complete.

## Test setup and isolation

- PHPUnit 12.5 (Laravel's feature/unit stack) runs through `php artisan test`/`composer test`.
- `phpunit.xml` sets test-only environment values. `Tests\TestCase::createApplication()` also overrides inherited shell environment variables **before Laravel boots**, forcing `APP_ENV=testing`, a test-only `APP_KEY`, array session/cache, sync queue, and SQLite `:memory:`. The base case asserts the environment/database or fails closed.
- Feature tests use `RefreshDatabase`; identities/tickets/catalogues are synthetic. No production/staff/student data or privileged seed account is used.
- Frontend assets are built by Vite. Blade responses are exercised in feature tests; this is not a browser-driven accessibility or E2E audit.

## Implemented coverage

The latest full run reported **36 tests passed (212 assertions)**, covering:

- Login page, valid/invalid credentials, inactive login/session denial, session ID rotation, logout invalidation/CSRF regeneration, guest-route protection, and rate limiting.
- Requester denial for account/location administration; account creation, normalized email, least-privilege role, password hashing, audit metadata, protected-field mass-assignment resistance, role assignment/revocation, self-change denial, deactivation/reactivation, last-admin safeguards, account search, and bootstrap command.
- Location screens and campus/zone/building/room hierarchy; parent/child rules, scoped uniqueness, normalization, cross-campus rejection, and restrictive foreign keys.
- Ticket creation sets requester and Open status server-side; requester sees own ticket but not another requester's record; requester queue query is scoped.
- Requesters cannot see internal notes or assigned technician identity and cannot create internal notes; public replies are allowed only for their own ticket. A regression test confirms broad ticket-view permission alone does not reveal internal notes, while assigned technicians can read authorized internal work notes.
- Helpdesk can view all tickets, manually assign only an active Technician, and advance status; assignment/status history rows are created.
- Technicians see and update only individually assigned tickets, may add internal work notes, cannot assign others, and cannot close tickets.
- Requesters can explicitly reopen only their own resolved/closed ticket; reopening clears current assignment and records assignment/status history.
- Category/priority catalogues start empty; System Administrator can configure categories, but has no ticket-content access; requester ticket intake supports optional configured category/priority.
- Ticket descriptions and comment bodies do not appear in audit metadata.
- Guest root redirection and the framework health endpoint.

## Actual checks performed

- `composer test` — **36 passed, 212 assertions**; test database is in-memory SQLite.
- `npm run build` — passed; Vite production assets generated.
- `composer audit --locked` — no security vulnerability advisories found; `npm audit --audit-level=moderate` — zero vulnerabilities found in the final Phase 2 verification.
- `./vendor/bin/pint --test` — passed for 87 PHP files; PHP syntax lint passed for the application, bootstrap, config, database, route, and test PHP files; `composer validate --no-check-publish` passed.

Commands/results describe this sandbox verification only. Do not infer that security review, MySQL/MariaDB migrations, CI, deployment, institutional UAT, or production readiness have been completed.

## Required later-phase tests

Before a pilot, add independent security/privacy review and browser-based accessibility/keyboard testing against CUT's approved standard; test SQL-engine-specific migrations and a fresh-install/upgrade path on CUT's selected database; review authorization for any regional/team additions; exercise inactive account/technician behavior; test all status/assignment concurrency paths; validate audit access/retention; and perform approved UAT and backup/restore rehearsal.

Later features require coverage for scoped team/deployment authorization and effective-date boundaries, unknown-location triage, attachments and malware scanning, notification retries/duplicate delivery, SLA calendars/clock behavior, privacy retention/export/deletion, asset integrations, and reporting definitions. Meaningful boundary assertions matter more than a percentage-only coverage threshold.
