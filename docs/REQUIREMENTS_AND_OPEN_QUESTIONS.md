# Requirements and Open Questions

**Purpose:** Distinguish user-provided requirements, implemented facts, engineering proposals, and institutional decisions not yet verified. As of 2026-10-10, Phases 1–4 are implemented locally; Phase 3 metadata and Phase 4 reporting remain provisional designs, not CUT ICT approval.

## Verified repository and sandbox facts

- Repository: `PraiseTechzw/CUT-ICT-HELPDESK`. Phase 0 and Phase 1 source briefs are preserved at `docs/PHASE0_BRIEF.md` and `docs/PHASE1_BRIEF.md`.
- Laravel **13.35.0**, PHP **8.3.6**, Composer **2.7.1**, Node.js **22.13.0**, npm **10.9.2**, and Vite **8.3.4** were observed in the implementation sandbox.
- Laravel Fortify provides login/logout. Blade, Tailwind CSS 4, Vite, Laravel Gates, Form Requests, and custom relational role/permission tables are used.
- Local application database is SQLite. PHPUnit tests use SQLite `:memory:` after the test harness forces `APP_ENV=testing` before Laravel boots. No CUT database, MySQL/MariaDB service, hosting, SSO, mail service, or production environment was verified.
- Latest full local run: **71 tests passed (536 assertions)**. Pint passed for 107 PHP files; Composer validation and Vite build succeeded. All 17 migrations ran on a fresh disposable SQLite database, which was removed. The latest dependency audits found no Composer advisories and zero npm vulnerabilities. See [Testing Strategy](TESTING_STRATEGY.md).
- Phase 1 commit `1878469`, Phase 2 commit `9e7ba09`, and Phase 3 commit `94e5b41` are on GitHub `main`. Phase 4 reporting remains local and uncommitted.

## User-provided requirements and approved implementation defaults

The supplied briefs describe a digital ICT support system for Chinhoyi University of Technology intended to complement physical walk-in helpdesk services. Phase 0 preferred a Laravel/PHP modular monolith with relational data and a Blade-first approach, subject to verified environment constraints.

The Phase 1 brief limits that phase to authentication, account/role administration, campus/zone/building/room administration, tests, and documentation. It explicitly deferred ticketing and assignments at that time.

On 2026-10-10, the user directed ticketing and technician workflows to Phase 2 and selected the conservative defaults presented for confirmation:

- Requesters create, view, and publicly reply only to their own tickets; they can explicitly reopen their own resolved/closed ticket.
- Helpdesk Officers can triage all tickets and manually assign an active individual Technician.
- Technicians can view/work only tickets individually assigned to them.
- Public replies and internal work notes are separate; requesters cannot read internal notes or technician identities.
- The proposed status flow is Open → Assigned → In Progress → Waiting on Requester → Resolved → Closed, with explicit reopen returning the ticket to the unassigned Open queue.
- Categories/priorities are configurable but empty by default; no fictional CUT catalogues are seeded.
- Team/zone-based routing, regional ticket authorization, SLAs, automatic routing, notifications, and email integrations are deferred.

This user approval authorized the implementation defaults for this repository. It does not confirm that CUT ICT has approved the global queue, role grants, state labels, category/priority owner, retention, or production use.

On 2026-10-10, the user approved a provisional Phase 3 foundation subject to these constraints: generic unseeded teams with no assumed zone mapping; multiple inclusive-dated technician memberships, allowing overlaps across teams but rejecting same-technician/same-team overlaps and duplicate starts; inclusive start/end date deployments to existing zones, allowing overlap as records only; `teams.manage` restricted to System Administrators; no tickets access, routing, assignment, escalation, schedule, or regional-scope changes; no destructive migrations; tests and documentation; and continued adaptation pending CUT confirmation.

On 2026-10-10, the user directed Phase 4 to reporting and analytics. The provisional implementation is limited to aggregate counts for tickets created within an inclusive date range: daily volume and current status/category/priority distributions. It reuses `tickets.view_all` (no new role grant), defaults to the last 30 calendar days, caps the range at 366 days, and omits ticket text/identities, record drill-down, and export. Definitions remain subject to CUT review.

## Implemented baseline and Phase 2/3 boundary

Authentication and admin workflows exist locally; roles/permissions are centrally seeded; locations form a campus → zone/building → room hierarchy with validation and database constraints; administrative audit events omit secrets and changed text values. No default administrator or fictitious institutional geography is seeded.

Phase 2 adds ticket/requester, category, priority, comment, status-history, and assignment-history records; requester/Helpdesk Officer/Technician permissions; manual assignment; controlled state transitions; public/internal comments; configurable empty catalogues; and safe audit events. Requester ownership and assigned-technician scopes are enforced in query and record policies. Internal-note reading uses an explicit ability separate from note creation and ticket visibility. System Administrators receive all permissions currently defined by the application, including ticket-content access.

Phase 3 adds generic teams, dated Technician memberships, and temporary deployments to existing zones as administrative metadata only. `teams.manage` is granted only to System Administrators. Membership/deployment records do not change Phase 2 ticket visibility, routing, assignment, or escalation; tickets still have no team/location fields and technicians remain authorized only by existing individual assignment rules.

Phase 4 adds aggregate ticket analytics behind the existing global Helpdesk Officer grant. It adds no ticket content/identity access, storage table, export, or new role permission. Other report audiences, metric definitions, timezone, small-cell suppression, service-level/productivity measurements, and export policy remain open. Routing, notifications, regional ticket authorization, attachments, SLAs, SSO, production integrations, and deployment remain unimplemented. No MySQL/MariaDB behavior is verified.

## Engineering proposals, not CUT approvals

Laravel 13/Fortify and the SQLite local default were selected for a testable bootstrap. The Phase 2 ticket MVP, Phase 3 metadata design, and Phase 4 analytics follow chat-approved/user-directed provisional choices, but the global helpdesk triage permission, status vocabulary, team structure, date rules, admin grants, report audience, report metrics, and timezone remain proposals until CUT review. Categories/priorities and team metadata can be configured by the seeded System Administrator role; these operator choices require institutional approval. Role assignments remain global; there are no regional ticket constraints.

## Unverified institutional assumptions and risks

The supported PHP/Laravel/database versions, hosting model, authentication source, identity lifecycle, email delivery, official brand assets and accessibility target, data classification, lawful basis, retention, backup/recovery, incident ownership, account recovery, location owners, ticket privacy, role grants, audit mandate, and release process remain unknown. Local test success does not establish secure production settings or MySQL compatibility.

The Phase 2 global Helpdesk Officer queue and unscoped ticket locations are significant data-access assumptions. Internal-note policy, requester identity/walk-in handling, catalogue ownership/values, status closure rules, privacy/retention, administrator content access, and the official team/regional model also need explicit CUT decisions before real data use. The Phase 3 metadata does not implement those access rules.

## Questions for CUT ICT before operational use

1. Who owns the product, approves account policy/roles, and signs off each release?
2. Which PHP, Laravel, SQL engine/version, hosting/network, and frontend/browser versions are supported?
3. Is SSO required? Which identity source, unique identifiers, test tenant, provisioning, deprovisioning, and account recovery process apply?
4. Who may create accounts, activate/deactivate users, assign privileged roles, and conduct emergency administrator recovery?
5. Are the proposed roles, labels, and grants correct? Must any role be scoped by campus/zone, or must separation of duties be stricter?
6. Should Helpdesk Officer access remain global, or be restricted by campus/zone/team? The current implementation gives System Administrators full application permissions; should this administrator-wide ticket access be retained for production?
7. Which campuses, operational zones, buildings, rooms, identifiers, and configuration owners are approved? How should unknown/off-campus locations be represented on tickets?
8. What teams, memberships, technician deployments, delegation, temporary assignment, and overlap semantics are authoritative?
9. How are walk-in requesters identified, and what is the minimum allowed personal data?
10. Which category and priority values, owners, ordering semantics, and mandatory/optional rules are approved?
11. Are the proposed ticket states and reopen/close rules correct? Which roles may resolve/close/reopen, and when should ownership change?
12. Are public replies and internal work notes sufficient? Who can read internal notes, and what retention/access controls apply?
13. Which service hours, SLA calendars, pause conditions, escalation pathways, and notification channels are approved?
14. What attachments, malware scanning, private storage, retention, deletion, and export rules apply?
15. What privacy classification, legal basis, retention schedule, user data rights, audit immutability/access, and security review are required?
16. What production database, credential management, backups, recovery objectives, monitoring, and incident-response controls are mandatory?
17. Is asset management in scope, or is another institution system the source of truth?
18. Which logo, colour palette, typography, accessibility standard, language, and device/browser matrix are approved?
19. Which test data, CI provider, dependency/secret scanning, code review, migration, and deployment process should be used?
20. Which reporting audiences and aggregate metrics are approved? What timezone, small-cell suppression, historical-status semantics, export, and retention rules apply?

Record each answer with approver, date, and evidence before translating it into production behavior. The Phase 2–4 implementation is a local prototype and must not be treated as CUT policy.
