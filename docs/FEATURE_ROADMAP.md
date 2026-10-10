# Feature Roadmap

**Status:** Phase 0 documentation and Phases 1–4 implementation are complete locally. Phases 3–4 contain provisional metadata/reporting designs pending CUT ICT review and validation against institutional technical/privacy requirements.

## Phase 0 — Foundation (complete)

**Delivered:** preserved source brief; scope, architecture, domain model, proposed permissions, setup/testing guidance, open questions, and phased roadmap. Institutional operations were not independently verified.

## Phase 1 — Application bootstrap, identity, roles, and regional configuration (implemented locally)

**Delivered:** Laravel 13 bootstrap; Fortify login/logout; active-account checks and rate limiting; proposed role/permission catalogue; account administration; guarded campus/zone/building/room configuration; safe administrative audit events; Blade screens, isolated PHPUnit tests, and setup/security documentation.

**Observed checks at Phase 1 completion:** 27 PHPUnit tests passed (144 assertions), Vite build succeeded, Pint passed, and dependency audits reported no advisories at the time. See [Testing Strategy](TESTING_STRATEGY.md). This does not establish production readiness, CUT approval, SQL compatibility, or security/UAT acceptance.

## Phase 2 — Ticket intake and individual technician workflow (implemented locally)

The user explicitly directed ticketing and technician workflows to Phase 2, moving ahead of the earlier roadmap's team-first staging. The user approved the conservative MVP defaults in chat on 2026-10-10; this is user authorization for the implementation, **not CUT institutional policy approval**.

**Delivered:** requester-owned ticket intake and views; global Helpdesk Officer triage queue; manual assignment to one active Technician; assigned-only technician views/work; separate public replies and internal notes; fixed status lifecycle with explicit reopen; transactional assignment/status histories; empty-by-default configurable categories and priorities; permission-guarded screens and privacy-focused tests.

**Approved implementation defaults:** Requesters read/reply only to their own tickets; technicians read/work only their assignments; helpdesk triage sees all tickets; public and internal comments are separate; lifecycle is Open → Assigned → In Progress → Waiting on Requester → Resolved → Closed; reopen is an explicit operation; categories/priorities are configurable with no invented seeds; no team/zone routing, SLA, auto-routing, notification, or email features.

**Observed checks:** 36 PHPUnit tests passed (212 assertions), Pint and PHP syntax lint passed, Composer validation passed, and Vite production assets built successfully. See [Testing Strategy](TESTING_STRATEGY.md). SQLite is the only exercised database engine. No migration has been rehearsed on a CUT-selected database.

**Not approved for real use:** The Helpdesk Officer queue is global; tickets do not carry campus/zone location. Exact role grants, catalogue values/owners, privacy/retention, internal-note governance, closure/reopen policy, and administrator ticket visibility need CUT review. System Administrator can configure the catalogues but receives no seeded ticket-content permission.

## Phase 3 — Teams, memberships, and technician deployments (implemented provisionally)

The user approved the low-impact foundation on 2026-10-10. **Delivered:** unseeded generic teams without zone mappings; dated technician memberships; date-bounded technician deployments to existing zones; System Administrator-only routes, Form Requests and Gates; safe audit events; and responsive admin screens. Membership dates are inclusive; null end means ongoing; overlaps are permitted across different teams but rejected for the same Technician/team; deployments require both dates and permit all overlaps, including exact duplicates. Referenced rows use restrictive foreign keys and no hard-delete routes.

**Strict access boundary:** membership and deployment data do not change ticket visibility, assignment, routing, or escalation. Phase 2 remains authoritative: Technicians can access tickets only when individually authorized under the existing rules. No `team_id` or location field was added to tickets.

**Observed checks at Phase 3 completion:** 45 tests passed (295 assertions); Pint passed for 101 PHP files; Composer validation and Vite build passed. All 15 then-current migrations ran on a fresh disposable SQLite database, whose file was removed after status verification. SQLite is the only exercised engine.

**Still requires CUT confirmation:** official team structure and ownership, membership/deployment operational meaning, overlap policy, delegation, regional access, and administration responsibilities. No CUT team names, staffing schedules, or zone boundaries were invented. This foundation must not be treated as institutional policy or production authorization.

## Phase 4 — Reporting and analytics (implemented provisionally)

The user re-scoped Phase 4 to reporting and analytics on 2026-10-10; the previous routing/notification workstream remains deferred. **Delivered:** a read-only aggregate report of tickets created over an inclusive date range; daily volume; current status, category, and priority counts; a 30-calendar-day default; a 366-day maximum; and an index supporting date-range queries. Only users with the existing global `tickets.view_all` permission may access it. No ticket identities/text, record drill-down, export, service-level calculation, productivity score, or new permission grant is introduced.

**Provisional definitions:** date bounds use the configured application timezone; all groupings include tickets created within the selected range; statuses are current at report time; category/priority display current catalogue labels. Audience, metrics, timezone, small-cell rules, export and retention remain subject to CUT ICT approval.

**Observed checks:** 49 tests passed (326 assertions); Pint passed for 107 PHP files; Composer validation and Vite build passed. All 17 migrations ran on a fresh disposable SQLite database, which was removed. SQLite is the only exercised engine.

## Phase 5 — Requester portal, technician workspace, and supervisor views (deferred)

**Prerequisites:** validated personas, mobile/browser support, accessibility requirements, user research, approved regional authorization, and CUT identity assets. **Deliverables:** responsive scoped workflows and supervisor views. **Tests:** end-to-end role journeys, keyboard accessibility, and regional data isolation.

## Phase 6 — Service targets, knowledge, and feedback (deferred)

**Prerequisites:** approved SLA definitions/calendars, article ownership/moderation, and feedback policy.

## Phase 7 — Assets and maintenance (deferred)

**Prerequisites:** source-of-truth decision, approved asset identifiers, custodianship, and integrations. Do not create a duplicate authoritative inventory without institutional approval.

## Phase 8 — Hardening, integrations, UAT, pilot, and deployment (not started)

**Prerequisites:** security/privacy review, hosting and backup plan, authorized integrations, support model, UAT, and explicit release approval. **Deliverables:** reviewed threat model, restore-tested backups, monitoring/runbooks, migration rehearsal, integration/regression/performance tests, and rollback/incident evidence. No deployment occurs as part of this implementation.

## Deferred workstream — Routing, escalation, communication, and notifications

The earlier roadmap placed these capabilities in Phase 4; they remain deferred after the user's Phase 4 re-scope. They require approved specialist coverage, service targets, escalation pathways, channels, delivery ownership, privacy, and retention. Public replies and internal notes exist, but external delivery and automated routing do not.

## Cross-phase exit discipline

Review schema, migrations, authorization boundaries, open questions, and meaningful automated tests before expanding the data surface. Record material changes and approval rationale. Phases 2–4 are implementation prototypes with user-approved or user-directed defaults; they must not be represented as CUT policy or production-ready behavior.
