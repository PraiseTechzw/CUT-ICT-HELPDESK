# Project Scope

**Status:** Phase 1 foundation is implemented locally from the user-provided briefs. Institutional workflows, role policy, geography, hosting, and visual identity remain unverified; see [Requirements and Open Questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md).

## Problem and long-term objective

The project is intended to complement CUT's physical walk-in ICT helpdesk with a digital service that can eventually register, coordinate, track, resolve, and report on authorized ICT support requests. Existing workflows, volumes, service commitments, regional staffing, and supporting systems have not been confirmed.

The long-term objective is a secure and maintainable base for requesters and staff across configurable locations, with historical records and authorization that reflect approved institutional practice.

## Phase 1 implementation scope

The current application implements login/logout and active-account enforcement; protected user/account administration; centralized proposed roles and granular permissions; administrator bootstrap without a seeded default user; administrative audit events; campus, operational-zone, building, and room records; server-side hierarchy/uniqueness validation; and feature tests/setup documentation.

The UI uses responsive Blade layouts, labeled forms, and reusable field-error presentation. Because approved CUT assets were not available, it uses neutral non-official styling and system fonts. No real staff/student records or institutional location names are supplied as seed data.

## Later capabilities (not implemented)

Ticket intake/categorization, requester and walk-in communications, assignment/transfer history, team membership, effective-dated technician deployments, scoped regional roles, routing/escalation, resolution/feedback, notifications, reporting, knowledge, assets/maintenance, identity-provider and email integrations, and production deployment remain later work. Team/user-zone relationships were intentionally not added because their institutional semantics are not confirmed.

## Stakeholders and proposed user groups

The briefs identify possible requesters (students, employees, other authorized people), walk-in intake staff, technicians across zones, regional supervisors, specialist teams, central ICT management, system administrators, and auditors. These are proposed personas; CUT ICT must confirm actual responsibilities and approvals.

## Scope boundaries and assumptions

The implementation is a Laravel modular monolith with Blade and relational storage. SQLite was used locally; MySQL/MariaDB and institutional hosting have not been verified. Role labels/grants, global rather than regional role assignments, and the password/account lifecycle are implementation proposals, not institutional policy. No SSO, LDAP, Active Directory, mail, or other internal integration is configured.

This phase does not implement ticketing or change production data/infrastructure. Before real use, CUT must confirm supported versions/hosting, identity source, account recovery, access policy, official locations and owners, privacy/retention/audit rules, approved CUT branding/accessibility standard, production database/backups, and security/release process.
