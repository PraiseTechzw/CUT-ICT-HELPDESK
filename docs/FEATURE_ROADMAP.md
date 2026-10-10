# Feature Roadmap

**Status:** Provisional ordering for planning; sequence must be adjusted after CUT ICT validates workflows and technical constraints. No feature below is implemented by this Phase 0 work.

## Phase 0 — Foundation (current)
**Deliverables:** repository inspection; preserved brief; scope, architecture, domain model, proposed permissions, setup/testing guidance, open questions, and roadmap. **Prerequisites:** none beyond repository access. **Data changes:** none. **Tests:** documentation/link and Git-diff checks only. **Acceptance:** repository state is accurately recorded, unknowns are visible, and next work is actionable. **Dependencies:** none. **Result:** documented; runtime/tooling is not installed.

## Phase 1 — Application bootstrap, identity, roles, and configuration
**Prerequisites:** approve Laravel/PHP versions, hosting constraints, package policy, identity source, role model, and data-protection requirements. **Deliverables:** compatible Laravel skeleton, local environment instructions, authentication, authorization policies, minimal user/role capability, secure configuration, CI baseline. **Data changes:** minimal user/role/permission structures only after design review. **Tests:** authentication, authorization denial, account lifecycle, session/security basics. **Acceptance:** clean install and automated tests pass; no default public/admin bypass; secrets excluded from Git. **Open institutional dependency:** SSO decision and account lifecycle ownership.

## Phase 2 — Campuses, locations, zones, teams, and technician deployments
**Prerequisites:** approved campus/location/zone model, administrators, team ownership, deployment semantics. **Deliverables:** configuration screens/API as appropriate and effective-dated associations. **Data changes:** campuses, locations, zones, teams, memberships, deployment history. **Tests:** relationship integrity, multi-zone memberships, effective dates/overlap behavior, unknown locations, authorization boundaries. **Acceptance:** admins can configure approved structures without code changes; historic references remain stable. **Dependency:** verified institutional geography and operations.

## Phase 3 — Ticket intake, catalogues, assignment, and histories
**Prerequisites:** confirmed intake channels, requester identity rules including walk-ins, category/priority/status catalogue, assignment authority, retention. **Deliverables:** create/view/update ticket workflow, queue, status and assignment history. **Data changes:** tickets, catalogues, assignments, status history. **Tests:** validation, state transitions, transactions, assignment history, requester ownership, staff-scoped access. **Acceptance:** walk-in and self-service creation (if approved) produce traceable records; changes preserve history. **Dependency:** approved process and privacy rules.

## Phase 4 — Routing, escalation, communication, and notifications
**Prerequisites:** approved zone routing, specialist coverage, service targets, escalation rules, communication channels and delivery ownership. **Deliverables:** deterministic routing, transfers, escalation events, public messages, private notes, notification outbox/retry behavior. **Data changes:** communication, escalation/SLA, notification records. **Tests:** routing matrix, duplicate delivery, permissions for internal notes, regional isolation, clock/calendar rules. **Acceptance:** every routing/transfer is auditable; no internal content leaks; failures are observable and recoverable. **Dependency:** ICT escalation policy and approved delivery providers.

## Phase 5 — Requester portal, technician workspace, supervisor views
**Prerequisites:** validated personas, accessibility/mobile requirements, supported browser/device matrix, user research. **Deliverables:** responsive workflows and scoped operational queues. **Data changes:** only those justified by tested UX requirements. **Tests:** end-to-end role flows, keyboard/accessibility checks, regional data isolation. **Acceptance:** representative users can complete approved tasks without seeing unauthorized records. **Dependency:** CUT-approved visual identity and usability review.

## Phase 6 — Service targets, knowledge base, reports, and feedback
**Prerequisites:** approved SLA definitions/calendars, reporting audience, metric definitions, article ownership/moderation, feedback policy. **Deliverables:** targets, versioned rules, knowledge articles, feedback, authorized reports. **Data changes:** target/rule versions, articles, feedback, report-safe aggregates as required. **Tests:** calculation boundaries, daylight/timezone rules, report access, content lifecycle. **Acceptance:** metrics can be reproduced and permissioned; rule changes do not rewrite historical outcomes. **Dependency:** ICT management sign-off.

## Phase 7 — Assets and maintenance
**Prerequisites:** decision on source of truth, approved asset identifiers, custodianship, links to external asset systems. **Deliverables:** asset registry or integration, maintenance records, ticket linkage if needed. **Data changes:** assets and maintenance history or mapping only. **Tests:** uniqueness, authorization, lifecycle, import/reconciliation if authorized. **Acceptance:** no duplicate authoritative inventory is created without institutional decision. **Dependency:** asset management ownership and data quality.

## Phase 8 — Hardening, integration, acceptance, pilot, deployment
**Prerequisites:** security/privacy review, hosting and backup plan, approved integrations, support model, rollout approval. **Deliverables:** threat review, restore-tested backups, monitoring, operational runbooks, UAT, pilot, deployment/release plan. **Data changes:** migrations reviewed and rehearsed. **Tests:** security/regression, performance appropriate to measured load, backup restore, integration tests, UAT. **Acceptance:** named approvers sign off; rollback and incident procedures are exercised; no deployment until explicitly authorized. **Dependency:** institutional change/release approval and verified infrastructure.

## Cross-phase exit discipline

Each phase should have reviewed migrations, access-control tests, updated docs, and observable acceptance evidence before the next expands the data surface. Sequence may change if confirmed institutional constraints require it; record material changes and rationale in a decision log before implementation.
