# Architecture

**Status:** Phases 1–5 are implemented and tested locally. Phase 3's teams/memberships/deployments, Phase 4's aggregate analytics, and Phase 5's audit/notification defaults are provisional; hosting, institutional policy, and production configuration have not been verified or approved.

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
- **Administrative audit:** append-oriented event records for account, role, location, ticket, comment, status, assignment, and catalogue actions. The `audit.view` grant gates an administrative view with action, actor-name, and inclusive local-calendar date filters. Ticket descriptions and comment bodies are never written to audit metadata. Eloquent instance updates/deletes are rejected; this is not database-enforced tamper-proof storage.
- **In-app notifications:** Laravel database notifications for ticket creation, assignment/status/reopen events, and comments. A dedicated service chooses active recipients using existing permissions and ticket assignment/ownership; the inbox is scoped to the signed-in user and checks `TicketPolicy` before offering a ticket link. Notification data contains a ticket ID, event code, and generic message only—never ticket subjects, descriptions, replies, or internal-note text. No email, SMS, or other external channel is enabled.
- **Team and deployment metadata:** unseeded generic teams, effective-dated Technician memberships, and date-bounded deployments to existing zones. Only System Administrators can manage these records; no team/zone mapping, ticket access, routing, schedule, or escalation behavior is inferred.
- **Reporting:** read-only aggregate counts for tickets created in a selected date range: daily volume, current status, category, and priority. Access reuses `tickets.view_all`; output contains no ticket identifiers, titles, descriptions, requester or technician identities, and no export/drill-down is provided.
- **Web presentation:** responsive Blade layouts and forms, accessible labels/validation messages, neutral styling, and local system fonts. No official CUT visual asset is present.

## Explicitly deferred boundaries

No team-based ticket assignment, user-zone grants, regional ticket authorization, automatic routing, escalation, SLAs/calendars, attachments, malware scanning, email/SMS notification delivery, assets, knowledge, historical-as-of reporting, service-level/productivity metrics, export, SSO, password-reset mail, production integrations, or deployment are implemented. Ticket records have no campus/zone ownership or cross-zone segregation yet. The helpdesk-wide triage permission is global in this MVP and must be reviewed before any operational use.

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
11. `teams.manage` is granted only to the proposed System Administrator role. Memberships require a current Technician-role account, team, and valid inclusive start/end dates. Blank membership end date means ongoing. Cross-team date overlaps are allowed; same-technician/same-team overlaps are rejected transactionally, and a unique index prevents duplicate starts. Deployments require an existing Technician-role account and zone plus a valid inclusive date range; all overlaps, including exact duplicate periods, are allowed as records. These records never affect ticket authorization.
12. `TicketNotificationService` is called from the ticket controller and transactional workflow. Laravel's database notification channel is the only channel; recipients are selected from current active roles, ownership, and assignment. The inbox queries only the authenticated user's notifications, and opening a ticket rechecks `TicketPolicy`. Audit filters use a Form Request requiring `audit.view`; date input is inclusive in the configured application timezone and converted to UTC for database comparisons.

## Data integrity

- Role and permission slugs are centrally defined in `App\Support\Roles` and `App\Support\Permissions` and seeded idempotently. System Administrators receive every currently defined permission; rerunning the role seeder applies newly added permissions.
- Campuses have globally unique codes; zones are unique within a campus; buildings are unique within a campus; rooms are unique within a building.
- Managed location codes are trimmed and uppercased before validation/storage.
- A building belongs to a campus and may have a zone only from that same campus; a composite foreign key enforces that relationship at the database layer.
- Ticket categories and priorities have unique slugs and sort order; no values are seeded. Ticket-to-catalogue foreign keys are optional and restrict deletion.
- A ticket has exactly one requester and at most one current technician assignee. Assignment history supports individual unassignment/reassignment; no team-based ticket assignment exists despite the separate Phase 3 Team metadata model.
- Ticket statuses are controlled strings from a centralized set. Assignment moves Open to Assigned; work moves Assigned to In Progress, In Progress to Waiting on Requester or Resolved, Waiting on Requester to In Progress or Resolved, and helpdesk triage moves Resolved to Closed. Reopening uses a separate action and returns to Open.
- Ticket comments and histories are append-oriented application records. Physical deletion of ticket records is not exposed.
- Audit events reject Eloquent instance updates and deletes and are displayed read-only, but bulk query updates/direct SQL and database-administrator access can bypass model hooks. Institutional retention, privileged access, integrity controls, and append-only storage requirements remain unapproved.

## Security and privacy boundaries

No public registration, default privileged user, SSO/LDAP/AD, reset mail, production secret, or external font service is configured. Passwords are hashed by Laravel; account/role controls are separated; self-role changes and self-deactivation are rejected. The active-account middleware ends sessions after deactivation. Requesters do not see internal notes, technician identities, or other requesters' tickets. Internal-note visibility is separately permissioned from ticket visibility and note creation. System Administrators receive all currently defined permissions, including ticket-content access. Audit payloads and notification payloads omit secrets and ticket/comment text; notification recipient and inbox access are independently checked. Audit model hooks provide application-level guardrails only, not tamper-proof or immutable database storage.

These controls are a development foundation, not a completed security/privacy review. Global Helpdesk Officer and System Administrator visibility, privacy classification/retention, audit immutability/access, session/deployment settings, cross-campus access rules, hosting/network protections, and production secret management require CUT ICT review. No regional access isolation is claimed.

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
| System Administrator permissions | All currently defined permissions | Ensure the administrative role can access every implemented application area; rerun the role seeder when grants change. |
| Teams and user-zone scope | Provisional metadata only: generic teams, dated memberships, zone deployments; no ticket access effects | CUT must confirm official structure, date/overlap semantics, delegation, and any future regional scope before operational use. |
| Reporting | Aggregate-only ticket counts, limited to existing global-triage permission; no record drill-down or export | Audience, metric definitions, timezone, small-cell policy, and export/retention rules require CUT approval. |
| Audit and notification delivery | Filtered audit view requires `audit.view`; generic ticket notifications use the database channel only | Audit access/retention/integrity and notification recipients, timing, and privacy require CUT review; no external channel is enabled. |
| Integrations | Deferred | Require authorized ownership, interfaces, credentials, and test environments. |

## Verification of provisional design decisions

This is a source/test consistency review, **not institutional approval**:

- **Phase 2 access defaults:** code and feature tests enforce requester ownership, assigned-technician-only access, global Helpdesk Officer triage, and full application permissions for System Administrators. The global triage and administrator access policies remain subject to CUT review.
- **Phase 3 teams and dates:** teams have no zone mapping and are not seeded. Memberships use inclusive calendar dates, allow cross-team overlap, reject same-technician/team overlap and duplicate starts, and permit null ongoing ends. Deployments reference existing zones, validate ordered inclusive dates, and permit overlaps, including exact duplicates.
- **Phase 3 administration and isolation:** the seeded `teams.manage` grant is System Administrator-only and enforced on routes/requests/controllers. Tests confirm memberships/deployments do not grant ticket access; ticket schema and `TicketPolicy` remain individually scoped. Migrations use restrictive foreign keys and membership uniqueness; a later forward migration removes the deployment exact-period uniqueness constraint without deleting rows. Rolling that migration back will fail safely if duplicate periods have since been recorded; no rows are deleted. Only disposable SQLite data was migrated.
- **Phase 4 reporting proposal:** the report reuses `tickets.view_all`, returns aggregates only, and adds no ticket-data grants, identities, ticket text, drill-down, export, or storage table. Date range and metric semantics are documented as provisional; tests cover role denial, privacy, grouping, boundaries, and range limits.
- **Phase 5 audit and notification proposal:** audit filtering is server-authorized by `audit.view`; action/actor filters and paired inclusive local-date filters are validated, bounded to 366 days, and converted to UTC for comparison. Audit entries omit ticket/comment content, and Eloquent instance updates/deletes are blocked but can be bypassed outside model events. Notifications are database-only, self-scoped in the inbox, policy-check ticket links, and contain only generic messages plus ticket IDs/event codes. Feature tests cover active-recipient selection, payload privacy, inbox ownership/opening, date boundaries, authorization, and model mutation guards. These are application defaults, not institutional policy.
- **Remaining unverified choices:** institutional role approval, regional access, team ownership, retention, report audiences/definitions/timezone, production SQL behavior, hosting/security configuration, and deployment remain unresolved.

## Current state

Laravel **13.35.0** and PHP **8.3.6** were observed in the sandbox. The current full suite reports **71 passing tests (536 assertions)**; Pint passed for 113 PHP files, Composer validation and Vite build passed, and all 18 migrations ran on a fresh disposable SQLite database. The database was removed after verification. Local success does not establish production readiness, institutional approval, or SQL-engine compatibility.
