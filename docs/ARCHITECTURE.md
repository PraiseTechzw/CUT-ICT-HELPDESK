# Architecture

**Status:** Phase 1 foundation and the user-approved Phase 2 ticketing MVP are implemented and tested locally. Hosting, institutional policy, and production configuration have not been verified or approved.

## Approach

The application is a **Laravel modular monolith** using one deployable web application, Eloquent, Blade, and a relational database. The implementation uses Laravel 13, Laravel Fortify for the authentication backend, server-side Blade views, Tailwind CSS, and Vite. It does not add a separate frontend client or a third-party role/permission package.

The repository's long-term target remains a SQL database appropriate to CUT-supported infrastructure. Only SQLite has been tested locally. Do not treat that local choice as approval of the production database engine.

## Implemented boundaries

- **Identity and access:** users, active/inactive status, proposed role/permission catalogues, password login/logout, gated administration, and command-line first-administrator bootstrap.
- **Regional configuration:** campuses, campus-scoped operational zones, buildings, optional building-to-zone association within the same campus, and building-scoped rooms.
- **Ticket intake:** authenticated requesters create tickets for themselves; ticket IDs are database IDs; category and priority references are optional and have no fabricated initial values.
- **Visibility and technician workflow:** requesters can view only their own tickets; Helpdesk Officers can view all tickets for triage; Technicians can view only tickets assigned to them. Assignment is manual and individual; only active accounts carrying the Technician role can be assigned.
- **Ticket communications:** public replies and internal work notes are distinct records. Requesters can read/reply publicly on their own tickets, but cannot read or create internal notes. Internal-note reading (`tickets.view_internal`) and writing (`tickets.comment_internal`) are separate abilities; both are granted only in the seeded MVP to Helpdesk Officers and Technicians, with record-level scope checks.
- **Workflow history:** status transitions and individual assignment/reassignment/release events are recorded transactionally. Reopening is explicit, allowed to the requester who owns the ticket or authorized helpdesk triage staff, clears any existing assignment, and returns the ticket to Open.
- **Configurable catalogues:** administrators can configure and deactivate categories/priorities. Values are not seeded, and referenced values are not physically deleted.
- **Administrative audit:** append-oriented event records for account, role, location, ticket, comment, status, assignment, and catalogue actions. Ticket descriptions and comment bodies are never written to the audit event metadata.
- **Web presentation:** responsive Blade layouts and forms, accessible labels/validation messages, neutral styling, and local system fonts. No official CUT visual asset is present.

## Explicitly deferred boundaries

No team membership, temporary technician deployments, user-zone grants, regional ticket authorization, automatic routing, escalation, SLAs/calendars, attachments, malware scanning, notifications/email delivery, assets, knowledge, reporting, SSO, password-reset mail, production integrations, or deployment are implemented. Ticket records have no campus/zone ownership or cross-zone segregation yet. The helpdesk-wide triage permission is global in this MVP and must be reviewed before any operational use.

## Authentication and request flow

1. Laravel's `web` middleware supplies session handling and CSRF protection to state-changing forms.
2. Fortify serves login/logout. Registration, profile mutation, and password-reset features are disabled. Successful authentication regenerates the session; logout invalidates it and regenerates the CSRF token using framework conventions.
3. Authentication and authorization remain separate. The `EnsureAccountIsActive` middleware invalidates a protected session for an account deactivated after login. Fortify refuses inactive accounts at login. Login attempts are rate-limited by email/IP.
4. Granular abilities are registered through Laravel Gates. `TicketPolicy` governs record-specific access and actions. Form Requests, route middleware, policy checks, and controller query scopes enforce authorization server-side; hiding a navigation link is not a security boundary.
5. Ticket index queries include only owned tickets, individually assigned tickets, or (for the designated helpdesk grant) all tickets. A guessed ticket ID that is outside the caller's visibility returns not found rather than displaying ticket content.
6. The ticket intake Form Request accepts subject, description, and optional active catalogue IDs only. Requester, status, and technician assignment come from server-controlled code, not client fields.
7. Comments have explicit `public` or `internal` visibility. Requesters receive only public comments; internal comments are queried only when the caller has the distinct `tickets.view_internal` grant and is an authorized triage officer or the ticket's assigned Technician. Internal-comment creation separately requires `tickets.comment_internal`. Blade escapes rendered content.
8. Assignment, status transitions, and reopening use database transactions with row locks and append status/assignment history. The allowed state transitions live centrally in `App\Support\TicketStatus` and workflow operations are implemented in `TicketWorkflow`.
9. Audit events record action, actor/subject, safe IDs/status/visibility metadata, or changed field names. Ticket descriptions and comment bodies are omitted.
10. Referenced records use restrictive deletes or are deactivated, preserving ticket history and catalogue references.

## Data integrity

- Role and permission slugs are centrally defined in `App\Support\Roles` and `App\Support\Permissions` and seeded idempotently. System Administrator grants are explicit; new ticket-data permissions are not implicitly inherited. In the default seed, a System Administrator can configure catalogues but does not gain ticket-content visibility.
- Campuses have globally unique codes; zones are unique within a campus; buildings are unique within a campus; rooms are unique within a building.
- Managed location codes are trimmed and uppercased before validation/storage.
- A building belongs to a campus and may have a zone only from that same campus; a composite foreign key enforces that relationship at the database layer.
- Ticket categories and priorities have unique slugs and sort order; no values are seeded. Ticket-to-catalogue foreign keys are optional and restrict deletion.
- A ticket has exactly one requester and at most one current technician assignee. Assignment history supports unassignment and reassignment without a team model.
- Ticket statuses are controlled strings from a centralized set. Assignment moves Open to Assigned; work moves Assigned to In Progress, In Progress to Waiting on Requester or Resolved, Waiting on Requester to In Progress or Resolved, and helpdesk triage moves Resolved to Closed. Reopening uses a separate action and returns to Open.
- Ticket comments and histories are append-oriented application records. Physical deletion of ticket records is not exposed.

## Security and privacy boundaries

No public registration, default privileged user, SSO/LDAP/AD, reset mail, production secret, or external font service is configured. Passwords are hashed by Laravel; account/role controls are separated; self-role changes and self-deactivation are rejected. The active-account middleware ends sessions after deactivation. Requesters do not see internal notes, technician identities, or other requesters' tickets. Internal-note visibility is separately permissioned from ticket visibility and note creation. The seeded System Administrator does not have ticket-content permissions. Audit payloads omit secrets and ticket/comment text.

These controls are a development foundation, not a completed security/privacy review. The proposed global Helpdesk Officer queue, administrator visibility, privacy classification/retention, audit immutability/access, session/deployment settings, cross-campus access rules, hosting/network protections, and production secret management require CUT ICT review. No regional access isolation is claimed.

## Architectural decisions

| Decision | Current implementation | Rationale / revisit trigger |
|---|---|---|
| Deployment shape | Laravel modular monolith | Fits the present bounded capabilities without distributed-service overhead. |
| Authentication | Laravel Fortify backend with custom active-user check | Maintained Laravel-native flow; reset and registration remain off until requirements exist. |
| Authorization | Central permission constants, relational roles/grants, TicketPolicy, Laravel Gates/Form Requests | Record-level requester/technician boundaries are testable; proposals still require institutional approval. |
| Frontend | Blade, Tailwind CSS, Vite | Server-rendered request and staff workflows; revisit only for validated interaction needs. |
| Local/test database | SQLite; tests use `:memory:` | Easy local setup and isolated tests. Production SQL remains undecided. |
| Ticket routing | Manual individual assignment only | Avoids inventing team, regional, or automatic routing semantics. |
| Ticket catalogues | Configurable tables with no seeded values | CUT categories/priorities are not known; a privileged local UI permits later configuration. |
| System Administrator ticket access | No ticket-content permission by default | Avoid implicit access escalation when new permission definitions are added. |
| Teams and user-zone scope | Deferred | Staffing, effective-date, delegation, and scope rules are not verified. |
| Integrations | Deferred | Require authorized ownership, interfaces, credentials, and test environments. |

## Current state

Laravel **13.35.0** and PHP **8.3.6** were observed in the sandbox. The current full test suite reports **36 passing tests (212 assertions)**; Pint, PHP syntax lint, Composer validation, and the Vite build passed. Dependency-audit details are in [Testing Strategy](TESTING_STRATEGY.md) and [Requirements and Open Questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md). Local success does not establish production readiness, institutional approval, or SQL-engine compatibility.
