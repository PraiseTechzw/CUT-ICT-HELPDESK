# Project Scope

**Status:** Phase 1 foundation and a user-approved Phase 2 ticketing MVP are implemented locally from the supplied briefs and chat-confirmed defaults. Institutional workflows, role policy, geography, hosting, and visual identity remain unverified; see [Requirements and Open Questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md).

## Problem and long-term objective

The project is intended to complement CUT's physical walk-in ICT helpdesk with a digital service that can eventually register, coordinate, track, resolve, and report on authorized ICT support requests. Existing workflows, volumes, service commitments, regional staffing, and supporting systems have not been confirmed.

The long-term objective is a secure and maintainable base for requesters and staff across configurable locations, with historical records and authorization that reflect approved institutional practice.

## Phase 1 implementation scope

The application implements login/logout and active-account enforcement; protected user/account administration; centralized proposed roles and granular permissions; administrator bootstrap without a seeded default user; administrative audit events; campus, operational-zone, building, and room records; server-side hierarchy/uniqueness validation; feature tests and setup documentation.

## Phase 2 implementation scope

The user approved a conservative ticketing MVP for Phase 2. It includes requester-owned ticket intake and view/reply, a global Helpdesk Officer triage queue, manual assignment to one active individual Technician, assigned-only Technician ticket access, public replies separated from internal notes, explicit state transitions and reopen, status/assignment history, and configurable category/priority catalogues with no invented initial values.

The UI uses responsive Blade layouts, labeled forms, and reusable field-error presentation. Requesters cannot see internal notes, technician identities, or other requesters' ticket data. The seeded System Administrator can configure catalogues but has no ticket-content permission by default.

## Out of scope

Team membership, effective-dated technician deployments, user-zone assignments, regional ticket authorization, location assignment on tickets, automatic routing, escalation, SLAs, attachments, email/notifications, reporting, knowledge, assets/maintenance, identity-provider integrations, external services, and production deployment remain deferred. No real staff/student records, CUT locations, categories, priorities, schedules, or assignments are supplied as seed data.

## Stakeholders and proposed user groups

The briefs identify possible requesters (students, employees, other authorized people), walk-in intake staff, technicians across zones, regional supervisors, specialist teams, central ICT management, system administrators, and auditors. These are proposed personas; CUT ICT must confirm actual responsibilities and approvals.

## Scope boundaries and assumptions

The implementation is a Laravel modular monolith with Blade and relational storage. SQLite was used locally; MySQL/MariaDB and institutional hosting have not been verified. The Phase 2 chat-approved defaults are implementation behavior for a prototype, not approved CUT policy. The Helpdesk Officer queue is global; technicians are scoped only to individual assignment, not region. Role grants and catalogue ownership/values remain adjustable proposals. No SSO, LDAP, Active Directory, mail, or other internal integration is configured.

Before real use, CUT must confirm supported versions/hosting, identity source, account recovery, role and ticket-access policy, approved locations and owners, privacy/retention/audit rules, category/priority/status semantics, official branding/accessibility standard, production database/backups, and security/release process.
