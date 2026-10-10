# Requirements and Open Questions

**Purpose:** Distinguish user-provided requirements, implemented facts, engineering proposals, and institutional decisions not yet verified. As of 2026-10-10, Phase 1 code exists and local checks pass; this document does not constitute CUT ICT approval.

## Verified repository and sandbox facts

- Repository: `PraiseTechzw/CUT-ICT-HELPDESK`. Phase 0 and Phase 1 source briefs are preserved at `docs/PHASE0_BRIEF.md` and `docs/PHASE1_BRIEF.md`.
- Laravel **13.35.0**, PHP **8.3.6**, Composer **2.7.1**, Node.js **22.13.0**, npm **10.9.2**, and Vite **8.3.4** were observed in the implementation sandbox.
- Laravel Fortify provides login/logout. Blade, Tailwind CSS 4, Vite, Laravel Gates, Form Requests, and custom relational role/permission tables are used.
- Local application database is SQLite. PHPUnit tests use SQLite `:memory:` after the test harness forces `APP_ENV=testing` before Laravel boots. No CUT database, MySQL/MariaDB service, hosting, SSO, mail service, or production environment was verified.
- The final completed test run reported 27 passing tests and 144 assertions. Vite build succeeded. Composer and npm dependency audits reported no advisories at the time of the checks. See [Testing Strategy](TESTING_STRATEGY.md).
- The first repository inspection predated the Laravel application. Current implementation details are recorded in [Architecture](ARCHITECTURE.md) and [Domain Model](DOMAIN_MODEL.md).

## User-provided requirements

The supplied briefs describe a digital ICT support system for Chinhoyi University of Technology intended to complement physical walk-in helpdesk services. The eventual system may serve requesters, intake officers, technicians, supervisors, specialists, ICT management, administrators, and auditors across configurable campus locations. The Phase 0 preference was a Laravel/PHP modular monolith with relational data and a Blade-first approach, subject to verified environment constraints.

The Phase 1 brief limits work to authentication, account/role administration, campus/zone/building/room administration, justified foundation relationships, tests, and documentation. It explicitly defers the full ticketing system, assignment engine, assets, knowledge, advanced reporting, and production integrations.

## Implemented Phase 1 baseline

Authentication and admin workflows exist locally; proposed roles/permissions are stored and centrally seeded; locations form a campus → zone/building → room hierarchy with server-side validation and database constraints; audit events record administrative actions without changed values or secrets. No default administrator or fictitious institutional geography is seeded. Account creation assigns only the proposed Requester role; the first administrator is created through an interactive, guarded Artisan command.

Public registration and reset/email flows are disabled; there is no SSO or account-recovery flow yet. Role names/grants are proposals. The implementation does not include teams, technician deployments, user-zone assignments, tickets, SLA, communications, production integrations, or database-specific verification beyond SQLite. Official CUT branding assets have not been supplied; styling remains neutral.

## Engineering proposals, not CUT approvals

Laravel 13/Fortify and the SQLite local default were selected for a testable bootstrap in this sandbox. User email and location codes are canonicalized by the application; proposed role codes are centralized; locations are deactivated rather than physically deleted in admin screens; role grants are global at present. These are implementation choices that require review against CUT technical standards and operating policy.

## Unverified institutional assumptions and risks

The supported PHP/Laravel/database versions, hosting model, authentication source, identity lifecycle, email delivery, official brand assets and accessibility target, data classification, lawful basis, retention, backup/recovery, incident ownership, account recovery, location owners, operational roles, audit mandate, and release process remain unknown. Local test success does not establish secure production settings or MySQL compatibility.

A consequential gap is that there is no password-reset or delegated account-recovery workflow. Another is that current role permissions are not location-scoped, because no ticket or staff assignment model exists. A third is that audits are application-level records with actor references, not an independently immutable or externally exported audit service.

## Questions for CUT ICT before operational use

1. Who owns the product, approves account policy/roles, and signs off each release?
2. Which PHP, Laravel, SQL engine/version, hosting/network, and frontend/browser versions are supported?
3. Is SSO required? Which identity source, unique identifiers, test tenant, provisioning, deprovisioning, and account recovery process apply?
4. Who may create accounts, activate/deactivate users, assign privileged roles, and conduct emergency administrator recovery?
5. Are the proposed roles, labels, and grants correct? Must any role be scoped by campus/zone, or must separation of duties be stricter?
6. Which campuses, operational zones, buildings, rooms, codes, and configuration owners are approved? How should unknown/off-campus locations be handled?
7. What teams, memberships, technician deployments, delegation, temporary assignment, and overlap semantics are authoritative?
8. How are walk-in requesters identified, and what is the minimum allowed personal data?
9. Which ticket categories, priorities, statuses, assignment authority, service hours, SLA calendars, pause conditions, and escalation pathways are approved?
10. Which messages/notes are public vs internal? Which notification channels and mail providers are approved and owned?
11. What attachments, malware scanning, private storage, retention, deletion, and export rules apply?
12. What privacy classification, legal basis, retention schedule, user data rights, audit immutability/access, and security review are required?
13. What production database, credential management, backups, recovery objectives, monitoring, and incident-response controls are mandatory?
14. Is asset management in scope, or is another institution system the source of truth?
15. Which logo, colour palette, typography, accessibility standard, language, and device/browser matrix are approved?
16. Which test data, CI provider, dependency/secret scanning, code review, migration, and deployment process should be used?

Record each answer with approver, date, and evidence before translating it into production behavior. The recommended next step is Phase 1 review and environment confirmation—not starting ticketing without those decisions.
