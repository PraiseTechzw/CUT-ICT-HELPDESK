# Testing Strategy

**Status:** Phases 1–5 have automated local coverage, including metadata-only Phase 3 boundaries, Phase 4 aggregate-report access/privacy, and Phase 5 notification/audit behavior. Institutional database compatibility, browser-based end-to-end/accessibility tests, independent security review, and UAT are not complete.

## Test setup and isolation

- PHPUnit 12.5 (Laravel's feature/unit stack) runs through `php artisan test`/`composer test`.
- `phpunit.xml` sets test-only environment values. `Tests\TestCase::createApplication()` also overrides inherited shell environment variables **before Laravel boots**, forcing `APP_ENV=testing`, a test-only `APP_KEY`, array session/cache, sync queue, and SQLite `:memory:`. The base case asserts the environment/database or fails closed.
- Feature tests use `RefreshDatabase`; identities/tickets/catalogues are synthetic. No production/staff/student data or privileged seed account is used.
- Frontend assets are built by Vite. Blade responses are exercised in feature tests; this is not a browser-driven accessibility or E2E audit.

## Implemented coverage

The latest full run reported **54 tests passed (363 assertions)**, covering:

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
- Ticket intake, assignment/status/comment notification recipients are selected from active accounts and existing permissions/ownership/assignment; notification records use only the database channel and their payloads omit ticket/comment text.
- Notification inbox data is scoped to the signed-in user; opening another user's notification returns not found, and opening an authorized ticket notification marks only the owner's notice read.
- Audit filters require `audit.view`, filter by action and actor name, include the date endpoints for inclusive local-calendar ranges, reject missing date pairs, and refuse unauthorized users. Eloquent instance updates and deletes of audit events fail; this does not cover bulk query operations or direct database writes.
- Phase 3 team administration is System Administrator-only; no teams/memberships/deployments are seeded; generic teams have no zone mapping.
- Membership dates are inclusive, nullable end dates mean ongoing, same-team overlap/duplicate periods are rejected, and overlapping periods across different teams are allowed. Database uniqueness and restrictive foreign keys are exercised.
- Deployments validate technician/zone relationships and date order, permit overlapping and exact duplicate periods, and preserve references. Memberships/deployments do not grant ticket access; assigned-only technician authorization remains intact.
- Reports are denied to Requesters, Technicians, and System Administrators; authorized Helpdesk Officers receive only aggregate date-range counts. Tests cover inclusive date boundaries, current-status/category/priority groupings, no ticket/requester text, empty reports, paired and ordered dates, 366-day limits, and malformed query values.
- Guest root redirection and the framework health endpoint.

## Actual checks performed

- `composer test` — **54 passed, 363 assertions**; test database is in-memory SQLite.
- `npm run build` — passed; Vite production assets generated.
- `composer audit --locked` — no security vulnerability advisories found; `npm audit --audit-level=moderate` — zero vulnerabilities found in the latest locked dependency audit.
- `./vendor/bin/pint --test` — passed for 113 PHP files; `composer validate --no-check-publish` passed.
- `npm run build` — passed; Vite production assets generated.
- Fresh-migration check: all 18 migrations ran and showed `Ran` on a new disposable SQLite database; the temporary database file was removed after verification. This is not MySQL/MariaDB or production migration verification.

Commands/results describe this sandbox verification only. Do not infer that security review, MySQL/MariaDB migrations, CI, deployment, institutional UAT, or production readiness have been completed.

## Required later-phase tests

Before a pilot, add independent security/privacy review and browser-based accessibility/keyboard testing against CUT's approved standard; test SQL-engine-specific migrations and a fresh-install/upgrade path on CUT's selected database; review authorization for any regional/team additions; exercise inactive account/technician behavior; test all status/assignment concurrency paths; validate audit retention/integrity and notification retention/reliability; and perform approved UAT and backup/restore rehearsal.

Later features require separate tests before any CUT-approved regional/team authorization is introduced, plus unknown-location triage, attachments and malware scanning, notification retries/duplicate delivery and external delivery if approved, SLA calendars/clock behavior, privacy retention/export/deletion, asset integrations, and expanded reporting after audience/metric/timezone approval. Meaningful boundary assertions matter more than a percentage-only coverage threshold.
