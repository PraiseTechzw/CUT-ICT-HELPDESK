# Testing Strategy

**Status:** Test plan for future implementation. There is currently no Laravel runtime, test configuration, application code, database connection, PHPUnit/Pest setup, or CI workflow in the repository. No automated application tests have been run.

## Test layers

- **Unit:** deterministic business rules, status transition validation, effective-date and overlap rules, priority/SLA calculations once approved. Avoid persistence when testing pure logic.
- **Feature:** HTTP intake, validation, authenticated request flows, assignment, status changes, communications, notifications, and reporting using Laravel's supported testing stack.
- **Authorization:** every role/capability should have allowed and denied cases; test route, API, resource, attachment, export, and report access—not only UI controls.
- **Database/integration:** constraints, migration behavior, transaction atomicity, assignment/status history, notification/outbox behavior, and external-provider adapters with fakes or approved test endpoints.
- **End-to-end/UAT:** later, exercise requester, walk-in registrar, technician, supervisor, specialist, manager, administrator, and auditor journeys with synthetic data and institution-approved acceptance scenarios.

## Required high-risk cases

1. Requester cannot view or change another requester's ticket unless an approved delegation rule applies.
2. Technician/supervisor cannot access tickets outside effective zone/team scope; cross-zone delegation works only when explicitly granted.
3. Unknown or unassigned location is accepted/routed only through the approved triage workflow and does not grant broad visibility.
4. A technician's normal team membership and temporary deployment are evaluated separately; expired/future dates and approved overlap behavior are tested.
5. Reassignment/transfer and status changes append history atomically; failed transactions leave neither partial current state nor misleading history.
6. Internal notes, private attachments, and audit-only fields never leak through requester views, notifications, search, reports, exports, or cached responses.
7. Walk-in ticket creator and requester are distinct and traceable where policy allows; identity minimization and validation are tested.
8. Invalid state transitions, forged actor/zone IDs, mass assignment, CSRF/authentication failures, and rate limits are handled safely.
9. Notification retries do not duplicate critical actions; delivery failure does not erase committed ticket state.
10. Migration upgrades and any approved rollback are tested on disposable databases with realistic but synthetic data.

## Data and test isolation

Use factories/fixtures with clearly fictional identities and locations; do not load real student/staff data into local or CI environments. Avoid production copies unless a separately approved de-identification process exists. Each test should control time for effective-date and SLA behavior, isolate database state, and avoid relying on test execution order. Do not include secrets or personal data in snapshots, logs, or assertions that surface in build output.

## Quality gates after bootstrap

Once tooling exists, select and document the project's PHPUnit or Pest choice, formatter, static-analysis/lint tools, and CI provider. A suitable CI sequence should install locked dependencies, validate formatting/static checks, run unit/feature/authorization tests, and build frontend assets if present. Pin compatible actions/runtime versions and scan dependencies according to institutional policy. No specific command is claimed to run before the scaffold exists.

## Acceptance evidence

For each feature, link requirement → implementation → tests → observed result. Regional isolation and privacy tests are release-blocking. Before pilot/release, add security review, approved UAT, backup restore rehearsal, migration rehearsal, and documented recovery/rollback evidence. Define expected coverage thresholds only after the team measures a maintainable baseline; coverage percentage does not replace meaningful assertions.
