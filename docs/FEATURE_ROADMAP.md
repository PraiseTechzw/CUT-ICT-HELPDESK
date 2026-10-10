# Feature Roadmap

**Status:** Phase 0 documentation is complete. Phase 1 is implemented and locally tested, pending CUT ICT review and validation against the institution's technical/policy requirements. No ticketing work has started.

## Phase 0 — Foundation (complete)

**Delivered:** preserved source brief; scope, architecture, domain model, proposed permissions, setup/testing guidance, open questions, and phased roadmap. The findings were based on the repository and user brief; institutional operations were not independently verified.

## Phase 1 — Application bootstrap, identity, roles, and regional configuration (implemented locally)

**Delivered:** Laravel 13 application bootstrap; Fortify login/logout; active-account checks, rate limiting, protected routes, test-safe session behavior, proposed role/permission catalogue, user search/create/profile/status/role administration, guarded campus/zone/building/room configuration, safe administrative audit events, Blade screens, isolated PHPUnit tests, and setup/security documentation.

**Observed checks:** 27 PHPUnit tests passed (144 assertions), Vite production asset build succeeded, Pint passed, and PHP/npm dependency audits returned no advisories at the time of execution. See [Testing Strategy](TESTING_STRATEGY.md). This does not establish production readiness, CUT approval, MySQL compatibility, or security/UAT acceptance.

**Open dependencies:** approve framework/hosting/database versions, identity and password recovery, proposed role grants, location ownership, privacy/retention/audit policy, accessibility/branding, and deployment/security controls. Teams, technician deployments, user-zone relationships, and per-region authorization remain unimplemented pending confirmed semantics.

## Phase 2 — Teams, memberships, and technician deployments

**Prerequisites:** CUT-approved team structure, staffing, home/ordinary membership, temporary deployment, effective-date/overlap, delegation, and administration rules. **Deliverables:** only the approved relationships and necessary administration. **Tests:** membership/deployment dates, overlap, scope authorization, historical integrity, and cross-zone access. No names, staffing schedules, or zone boundaries should be invented.

## Phase 3 — Ticket intake, catalogues, assignment, and histories

**Prerequisites:** confirmed intake channels, walk-in identity/minimization rules, category/priority/status catalogues, assignment authority, and retention. **Deliverables:** create/view/update ticket, queue, assignment/status history. **Tests:** validation, state transitions, transactions, requester ownership, and staff-scoped access.

## Phase 4 — Routing, escalation, communication, and notifications

**Prerequisites:** approved routing, specialist coverage, targets, escalation, channels, and delivery ownership. **Deliverables:** deterministic routing/transfers, public messages, internal notes, and notification retry behavior. **Tests:** routing matrix, duplicate delivery, privacy boundaries, and clock/calendar rules.

## Phase 5 — Requester portal, technician workspace, and supervisor views

**Prerequisites:** validated personas, mobile/browser support, accessibility requirements, user research, and CUT-approved identity assets. **Deliverables:** responsive workflows and scoped operational queues. **Tests:** end-to-end role journeys, keyboard accessibility, and regional data isolation.

## Phase 6 — Service targets, knowledge, reports, and feedback

**Prerequisites:** approved SLA definitions/calendars, reporting audience, metric definitions, article ownership/moderation, and feedback policy. **Deliverables:** versioned targets, articles, feedback, and authorized reports with reproducible metrics.

## Phase 7 — Assets and maintenance

**Prerequisites:** source-of-truth decision, approved asset identifiers, custodianship, and integrations. Do not create a duplicate authoritative inventory without institutional approval.

## Phase 8 — Hardening, integrations, UAT, pilot, and deployment

**Prerequisites:** security/privacy review, hosting and backup plan, authorized integrations, support model, UAT, and explicit release approval. **Deliverables:** reviewed threat model, restore-tested backups, monitoring/runbooks, migration rehearsal, integration/regression/performance tests, and rollback/incident evidence. No deployment occurs as part of this implementation.

## Cross-phase exit discipline

Complete review of schema, migrations, authorization boundaries, documented open questions, and meaningful automated tests before expanding the data surface. Record material changes and approval rationale in project documentation. Do not proceed from Phase 1 directly into ticketing until the foundation and required institutional decisions have been reviewed.
