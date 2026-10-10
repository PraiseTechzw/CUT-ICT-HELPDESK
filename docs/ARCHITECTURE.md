# Architecture

**Status:** Implemented Phase 1 foundation, pending CUT ICT review. The framework and local flows have been verified; hosting, institutional policy, and production configuration have not.

## Approach

The application is a **Laravel modular monolith** using one deployable web application, Eloquent, Blade, and a relational database. The implementation uses Laravel 13, Laravel Fortify for the authentication backend, server-side Blade views, Tailwind CSS, and Vite. It does not add a separate frontend client or a third-party role/permission package.

The repository's long-term target remains a SQL database appropriate to CUT-supported infrastructure. Only SQLite has been tested locally. Do not treat that local choice as approval of the production database engine.

## Implemented boundaries

- **Identity and access:** users, active/inactive status, proposed role/permission catalogues, password login/logout, gated administration, and command-line first-administrator bootstrap.
- **Regional configuration:** campuses, campus-scoped operational zones, buildings, optional building-to-zone association within the same campus, and building-scoped rooms.
- **Administrative audit:** append-oriented event records for account, role, and location administration. Metadata contains action/context and field names, not password or changed-field values.
- **Web presentation:** responsive Blade layouts and forms, accessible labels/validation messages, neutral styling, and local system fonts. No official CUT visual asset is present.

## Explicitly deferred boundaries

Ticket lifecycle, requester communications, assignment and escalation, teams, memberships, temporary technician deployments, per-zone user grants, service catalogues, SLAs, notifications, uploads, assets, knowledge, reports, SSO, email/password-reset delivery, and production integrations are not implemented. The Phase 1 prompt permits deferring team/deployment tables where the governing model and staffing semantics are not approved; those relationships remain documented proposals, not schema.

## Authentication and request flow

1. Laravel's `web` middleware supplies session handling and CSRF protection to state-changing forms.
2. Fortify serves login/logout. Registration, profile mutation, and password-reset features are disabled. Successful authentication regenerates the session; logout invalidates it and regenerates the CSRF token using framework conventions.
3. Authentication and authorization remain separate. The `EnsureAccountIsActive` middleware invalidates a protected session for an account deactivated after login. Fortify refuses inactive accounts at login. Login attempts are rate-limited by email/IP.
4. A route, middleware, controller authorization check, or Form Request enforces the required granular ability server-side. Hiding a navigation link is not the security boundary.
5. Form Requests validate normalized user email/location codes and parent-child relations before data changes. User profile fields are separate from role/status actions.
6. Multi-write administration operations use database transactions. The audit logger records actor/action/subject and safe field-name metadata.
7. Blade escapes displayed values; read-only screens limit access using the same permission abilities. Referenced locations are deactivated rather than physically deleted through the UI, and foreign keys restrict damaging deletes.

## Data integrity

- Role and permission slugs are centrally defined in `App\Support\Roles` and `App\Support\Permissions` and seeded idempotently.
- Campuses have globally unique codes; zones are unique within a campus; buildings are unique within a campus; rooms are unique within a building.
- Managed location codes are trimmed and uppercased before validation/storage.
- A building belongs to a campus and may have a zone only from that same campus; a composite foreign key enforces that relationship at the database layer.
- Users have global role assignments only. No campus/zone-scoped staff assignments exist yet.
- Referential deletes are restricted for location hierarchy. Audit actor references become null if an account is later deleted, preserving the event.

## Security and privacy boundaries

No public registration, default privileged user, SSO/LDAP/AD, reset mail, production secret, or external font service is configured. Passwords are hashed by Laravel; account/role controls are separated; self-role changes and self-deactivation are rejected. The active-account middleware ends sessions after deactivation. Audit payloads deliberately omit secret and changed-field values.

These controls are a development foundation, not a completed security/privacy review. Institutional password policy, account recovery, data classification/retention, audit immutability/access, session encryption/deployment settings, hosting/network protections, and production secret management remain CUT ICT decisions. Ticket privacy and cross-zone record authorization are future-phase concerns and are not claimed as implemented.

## Architectural decisions

| Decision | Current implementation | Rationale / revisit trigger |
|---|---|---|
| Deployment shape | Laravel modular monolith | Fits the present bounded capabilities without distributed-service overhead. |
| Authentication | Laravel Fortify backend with custom active-user check | Maintained Laravel-native flow; reset and registration remain off until requirements exist. |
| Authorization | Central permission constants, relational roles/grants, Laravel Gates/Form Requests | Granular and testable; no unnecessary package; proposals require institutional approval. |
| Frontend | Blade, Tailwind CSS, Vite | Server-rendered admin workflows; revisit only for validated interaction needs. |
| Local/test database | SQLite; tests use `:memory:` | Easy local setup and isolated tests. Production SQL remains undecided. |
| Code catalogue | Idempotent seeder for proposed role/permission reference data | No real or privileged sample accounts. |
| Teams and user-zone scope | Deferred | Staffing, effective-date, delegation, and scope rules are not verified. |
| Integrations | Deferred | Require authorized ownership, interfaces, credentials, and test environments. |

## Current state

Laravel **13.35.0** and PHP **8.3.6** were observed in the sandbox. The implementation has PHPUnit feature/unit coverage, and Vite production assets build successfully. Dependency audits and detailed test evidence are in [Testing Strategy](TESTING_STRATEGY.md) and [Requirements and Open Questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md). Local success does not establish production readiness or institutional approval.
