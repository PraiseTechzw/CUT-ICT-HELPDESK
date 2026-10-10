# Implemented and Deferred Domain Model

**Status:** Phases 1–4 are implemented and tested with SQLite. Phase 3 team/membership/deployment semantics and Phase 4 report definitions/audience are provisional implementation choices, not CUT policy. Role names/grants, institutional privacy rules, ticket catalogue values, location ownership, reporting timezone, and production database behavior still require CUT ICT review.

## Implemented entities

- **User:** account with name, unique email, password hash, timestamps, and `is_active`. No public registration or real user fixture is seeded. User role assignments are global, not campus/zone-scoped.
- **Role / Permission:** unique role and permission slugs, names/descriptions, and many-to-many `role_user` and `permission_role` pivots with uniqueness constraints and timestamps. Definitions are centrally named and idempotently seeded. Role assignments are separate from profile fields.
- **Team:** generic administrator-configured name, unique slug, optional description, active flag, and timestamps. No team names are seeded, and teams have no assumed campus/zone mapping.
- **TechnicianTeamMembership:** Technician user, team, inclusive `starts_on`, nullable inclusive `ends_on`, and timestamps. A null end means ongoing. A technician may have overlapping memberships in different teams; overlapping periods for the same technician/team are rejected, and a database unique index prevents duplicate start dates for that pair.
- **TechnicianDeployment:** Technician user, existing operational-zone reference, required inclusive `starts_on`/`ends_on`, and timestamps. All overlaps, including exact duplicate technician/zone/date periods, are permitted and are not interpreted as schedules, exclusive assignments, or authorization grants.
- **Campus / OperationalZone / Building / Room:** configurable campus hierarchy; a zone belongs to a campus, a building belongs to a campus and may reference a zone from that same campus, and a room belongs to a building. Parent references and scoped code uniqueness are enforced; locations are deactivated rather than deleted through the UI.
- **TicketCategory / TicketPriority:** configurable name, unique slug, sort order, active flag, and timestamps. Both catalogues begin empty; no CUT-specific values are fabricated. Deactivation preserves historical references.
- **Ticket:** one requester, an optional individual technician assignee, optional category and priority references, subject, description, controlled status, and timestamps. The current schema does not associate a ticket with a campus, zone, building, or room.
- **TicketComment:** ticket, author, body, visibility (`public` or `internal`), and timestamps. Public requester-facing replies and internal work notes use separate visibility values in the same relational table.
- **TicketStatusHistory:** ticket, nullable prior status for initial creation, new status, nullable actor reference, and timestamps. Creation, assignment-driven state changes, work transitions, closing, and explicit reopening are represented.
- **TicketAssignmentHistory:** ticket, nullable previous and next individual technician IDs, actor, and timestamps. Assignment, reassignment, unassignment, and reopen release are retained.
- **AuditEvent:** nullable actor foreign key (`nullOnDelete`), action, optional subject type/id, safe JSON metadata, and indexed creation time. Audit rows omit ticket descriptions and comment bodies.
- **Ticket analytics:** query-time aggregates over existing tickets; there is no reporting fact table or per-ticket report export. The selected creation-date range is inclusive (default last 30 calendar days, maximum 366); daily volume and category/priority groups use current referenced labels, and status groups reflect current status at query time.

Ticket requests are created with status `open`; requester ID and status are set server-side. Optional catalogue IDs must reference active values. User records are deactivated rather than deleted, and foreign keys preserve request/history relationships. Reports run aggregate queries over tickets and use a `created_at` index; no analytics rows or snapshots are stored.

## Ticket lifecycle and assignment

The central statuses are:

| Status | Meaning |
|---|---|
| `open` | Newly created or returned to the unassigned queue. |
| `assigned` | Assigned to one active Technician. |
| `in_progress` | Technician/helpdesk work is underway. |
| `waiting_for_requester` | Support is waiting for the requester. |
| `resolved` | Support considers the issue resolved; eligible for closure or explicit reopening. |
| `closed` | Closed by authorized helpdesk staff; eligible for explicit reopening. |

Assignment of an Open ticket sets it to Assigned. Ordinary transitions are Assigned → In Progress; In Progress → Waiting on Requester or Resolved; Waiting on Requester → In Progress or Resolved; and Resolved → Closed by helpdesk staff. Requesters and helpdesk triage can use the separate reopen action on a resolved/closed ticket; reopening clears its current technician and returns it to Open. Technicians cannot close or reopen tickets. Each state/assignment change is transacted with its history row.

There is no team/queue-based ticket assignment, regional ticket scope, automatic routing, SLA, or notification record in this model. Phase 3 memberships and deployments are administrative records only and cannot change ticket access. Helpdesk visibility is global for the seeded Helpdesk Officer permission; technicians see only tickets individually assigned to them; requesters see only their own.

## Integrity and privacy rules

1. Role/permission pivots reject duplicate grants. The role seeder synchronizes proposed grants deterministically.
2. Location codes are normalized; location parent-child links and scoped code uniqueness are validated at the application and database layers.
3. Ticket requester, current assignee, subject, and status are never accepted from the requester form as trusted values. The controller sets requester/status; only the assignment workflow sets the assignee.
4. Only active users with the Technician role may receive an assignment. Assignment authority is limited to the seeded helpdesk triage permission.
5. Query scopes and `TicketPolicy` permit owners, authorized global helpdesk triage, and the individually assigned Technician only. Guessed IDs outside a caller's visibility return not found.
6. A requester sees only public comments. Internal notes are queried for helpdesk triage staff or the assigned Technician; output is HTML-escaped.
7. Audit metadata excludes ticket text, descriptions, and comment content. Public/internal visibility and non-secret workflow metadata may be recorded.
8. Ticket and catalogue physical deletion is not exposed; referenced rows use restrictive deletes. Reopen, unassignment, and reassignment leave explicit history.
9. Feature tests use synthetic identities and SQLite in-memory isolation.
10. `teams.manage` is granted only to System Administrators. Membership changes are transactionally checked for same-team date overlap; deployments must reference existing technicians/zones, validate ordered dates, and permit all overlaps, including exact duplicates. Restrictive foreign keys retain membership/deployment references. These records do not add `team_id` or zone fields to tickets or change `TicketPolicy`.

## Deferred entities and decisions

No user-zone pivot, ticket-location fields, team-based routing, SLA/calendar, escalation rule, attachment/upload, asset, knowledge, notification, or reporting fact table exists. The current aggregate report is derived from existing tickets and adds no record-level view. Tickets are not region-scoped, and no ticket location or walk-in proxy identity is captured. The generic team/membership/deployment schema and report definitions are provisional; CUT must still confirm official organization structure, responsible administrators, regional delegation, privacy/retention, reporting audience/timezone/metric definitions, and whether future records should influence access.

The category and priority values are empty. The System Administrator role can configure those reference lists, but by default does not receive ticket-content permissions. Exact values, catalogue owner, priority interpretation, and whether other roles should configure catalogues require approval.

## Decisions still open

Confirm approved campus/zone/location identifiers and configuration owners; exact role titles and grants; whether ticket access must be scoped by campus/zone; team membership vs temporary deployment semantics and overlap rules; walk-in identity/minimization rules; categories and priority ordering; service hours, SLA calendars and pause conditions; escalation and assignment/transfer authority; status terminology and closure/reopen timing; message retention/legal basis, attachment handling, audit access/immutability; identity source and account recovery; report audience, metric definitions, timezone, small-cell suppression, export/retention rules; and production database/backup choices. The schema and report definitions are testable proposals, not institutional approval.
