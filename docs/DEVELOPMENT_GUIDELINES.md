# Development Guidelines

**Status:** Applied to the Laravel 13 Phase 1 foundation, Phase 2 ticketing MVP, and user-approved provisional Phase 3 metadata; continue to separate implementation choices from CUT decisions.

## General principles

Prefer readable Laravel conventions, strict typing where appropriate, explicit names, and focused files. Keep controllers focused on HTTP coordination; validate through Form Requests; enforce access through Gates, middleware, and server-side checks. Use a service/action only where it separates a meaningful workflow. Avoid giant controllers/templates, duplicated rules, speculative abstractions, unnecessary dependencies, and commented-out code. Comments should explain non-obvious decisions.

## Naming and organization

Follow the installed Laravel conventions for namespaces, routes, migrations, factories, and tests. Keep HTTP code in `app/Http`, policies/middleware/providers/support in their framework-appropriate locations, models in `app/Models`, migrations/seeders/factories in `database`, routes in `routes`, views/assets in `resources`, and tests under `tests`. Role and permission identifiers live in `App\Support\Roles` and `App\Support\Permissions`; do not scatter authorization decisions as role-name checks.

## Validation, authorization, and data exposure

Validate untrusted inputs on the server. Route IDs, role IDs, location parents, catalogue IDs, status, visibility, technician/team/zone IDs, and effective dates are untrusted. Derive authority from the authenticated actor and persisted relationships; do not rely on hiding buttons. Phase 3 team administration uses a separate System Administrator-only ability; its records do not grant ticket permissions. Validate inclusive date ranges, open-ended memberships, duplicate starts, and same-team overlap server-side and preserve foreign-key references. Ticket code continues to enforce requester ownership and assigned-Technician scope; no regional scope exists. Keep private notes out of requester responses and exports, and do not implement attachments before storage/scanning/retention controls are approved. Never put plaintext passwords, real personal data, or production secrets in tickets, logs, fixtures, Git, screenshots, or documentation.

## Database and migrations

Treat shared migrations as history: add a corrective migration instead of rewriting one already applied in shared environments. Use foreign keys, nullability, uniqueness, and indexes; preserve location hierarchy, membership/deployment references, and ticket histories with restrictive deletion/deactivation. Membership periods are inclusive; null end means ongoing; same-team overlap checks run inside a serialized transaction. Use transactions when state and an audit/history record must commit together. Seed deterministic role/permission catalogues only; ticket catalogues and teams remain empty until configured. Do not fabricate CUT zones, staff, catalogue values, teams, or schedules. Exercise migrations against isolated databases and verify rollback where safe.

## Testing and quality

The project uses PHPUnit via `php artisan test`, Laravel Pint, Vite, and Tailwind CSS 4. Tests force `APP_ENV=testing` and SQLite `:memory:` before application boot and fail closed if isolation is not active. Use synthetic fixtures; do not copy production data. Run `php artisan test`, `./vendor/bin/pint --test`, `npm run build`, and the Composer/npm audits after changes. Capture actual command outcomes; do not describe a check as passed until it ran successfully.

The present suite covers Phase 1 authentication/account/role/location administration, Phase 2 ticket boundaries/workflows, and Phase 3 admin-only team metadata, inclusive dates, duplicate/overlap rules, relationships, and no ticket-access expansion. It does not cover CUT-approved regional behavior, MySQL compatibility, production configuration, browser E2E, UAT, concurrency under another database engine, or independent security review.

## Code review checklist

- Does the change meet an explicitly approved requirement without inventing CUT policy?
- Are authorization and validation enforced server-side on every route/action?
- Are relational constraints, transaction boundaries, history retention, and migration rollout safe?
- Are logs, responses, notifications, uploads, exports, and exceptions free of secrets and unauthorized data?
- Are tests isolated, deterministic, synthetic, and meaningful at security boundaries?
- Are accessibility and approved CUT branding considered without presenting guessed identity as official?
- Are setup/open-question docs current and observed results truthful?
- Are dependencies maintained, compatible, justified, and license-reviewed?
