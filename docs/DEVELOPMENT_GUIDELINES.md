# Development Guidelines

**Status:** Proposed; apply after the Laravel version and tooling are approved and the app is initialized.

## General principles

Prefer readable Laravel conventions, cohesive responsibilities, strict typing where appropriate, explicit names, and small focused files. Keep controllers focused on HTTP coordination; validate through Form Requests; enforce access through policies/middleware and server-side checks; keep workflows in a service/action only when that separates meaningful business behavior. Models express relationships and persistence behavior but should not become giant workflow containers. Avoid giant controllers/templates, unrelated helpers, duplicate rules, speculative abstractions, unnecessary dependencies, and commented-out code. Comments should explain non-obvious decisions.

## Naming and organization

Follow the selected Laravel version's conventions for namespaces, classes, routes, migrations, factories, and tests. Keep HTTP code in `app/Http`, policies in `app/Policies`, models in `app/Models`, notifications in `app/Notifications`, migrations/seeders/factories in `database`, web/API routes in `routes`, UI assets in `resources`, and tests under `tests`. Add cohesive domain folders only where they help navigation. Use descriptive route names and enum/config values from a reviewed central source rather than magic strings scattered through views.

## Validation, authorization, and data exposure

Validate every untrusted input on the server. Treat route IDs, `user_id`, `zone_id`, role and assignment fields from clients as untrusted; derive authority from the authenticated actor and persisted scope. Deny by default and test forbidden access as well as allowed access. Do not rely on hiding buttons in the UI. Keep requester-visible communication distinct from internal notes at query, policy, resource/serialization, notification, export, and attachment layers. Never put plaintext passwords or production secrets in ticket text, logs, fixtures, Git, screenshots, or documentation.

## Database and migrations

Migrations are forward-only shared history: do not edit a migration already applied in shared environments; add a new corrective migration. Use foreign keys, appropriate nullability, uniqueness and indexes; document destructive or locking changes and provide a safe rollout/backfill plan. Preserve ticket assignment/status history and avoid destructive cascades. Use transactions when a business event and its history must commit atomically. Seed only deterministic reference data after catalogue approval; never insert fictional CUT zones, real staff, or production-like personal data. Test migrations on an isolated database and verify rollback where safe.

## Testing and quality

Use the existing supported PHPUnit or Pest choice after bootstrap; don't add a test framework before checking Laravel defaults. Write focused unit tests for pure rules and feature tests for HTTP behavior. Every permission boundary requires positive and negative tests, particularly regional isolation and internal-note privacy. Cover transitions, reassignment history, unassigned locations, temporary deployments, notification failures, and transaction rollback as features arrive. Keep fixtures synthetic and minimal. Run formatter/static analysis/lint commands only once they exist; record actual commands and outcomes.

## Code review checklist

- Does the change meet an approved requirement and avoid unconfirmed institutional policy?
- Is the change in the right responsibility/module, consistent with the chosen Laravel version?
- Are validation, server-side authorization, regional scope, and internal/private data visibility enforced and tested?
- Are database constraints, transaction boundaries, history retention, indexes, and migration rollout safe?
- Are logs, responses, notifications, attachments, exports, and errors free of secrets and unauthorized data?
- Are tests deterministic, readable, and based on synthetic data?
- Are accessibility, responsive behavior, and approved CUT branding considered for UI changes?
- Are setup/docs/open questions updated, and are commands/results described truthfully?
- Are dependencies justified, maintained, compatible, and license-reviewed?
