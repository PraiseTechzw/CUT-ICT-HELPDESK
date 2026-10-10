# Implemented and Deferred Domain Model

**Status:** Phase 1 schema is implemented in Laravel migrations and tested with SQLite. Role names, grants, institutional account policy, location ownership, and production database behavior still require CUT ICT review.

## Implemented entities

- **User:** Laravel account with name, unique email, password hash, timestamps, and an `is_active` status added through a new migration. No public registration or real user fixture is seeded. User role assignments are global, not campus/zone-scoped.
- **Role / Permission:** unique role and permission slugs, names/descriptions, and many-to-many `role_user` and `permission_role` pivots with uniqueness constraints and timestamps. Definitions are centrally named and idempotently seeded. Role assignments are handled separately from profile fields.
- **Campus:** name, globally unique code, optional description, active status, and timestamps.
- **OperationalZone:** belongs to one campus; code uniqueness is scoped to that campus; optional description, active status, and timestamps.
- **Building:** belongs to a campus and may reference an operational zone. Codes are unique within campus. A composite foreign key requires any zone to belong to the same campus as the building.
- **Room:** belongs to a building; room code is unique within that building. Campus context is derived through the building.
- **AuditEvent:** nullable actor foreign key (`nullOnDelete`), action, optional subject type/id, safe JSON metadata, and indexed creation time. Actor identity can be null on subsequent account deletion; event history remains.

Codes entered through the application are trimmed and uppercased before validation. Names/descriptions and active status are validated server-side. Location references use restrictive deletion; administrative screens offer deactivation instead of physical delete.

## Not implemented (intentionally deferred)

No **TechnicalTeam**, **TeamMembership**, **TechnicianDeployment**, user-zone pivot, ticket, assignment/status history, service category, priority, message/work log, SLA/escalation, notification, asset, or knowledge tables exist. The Phase 1 prompt says to build team/deployment tables only if the approved domain model makes them foundational. CUT has not confirmed team membership, temporary deployment, overlap, cross-zone delegation, or ownership rules, so adding them now would encode invented operations. Tickets and their downstream records are explicitly out of scope.

## Integrity and transaction rules

1. Foreign keys and unique indexes protect location and role relationships; deleting a parent location with children is restricted.
2. Role and permission joins reject duplicates. Seeder synchronization makes the proposed catalogue deterministic.
3. User creation, role synchronization, status changes, and location changes are coordinated transactionally with safe audit events.
4. Account profile update rules allow only name/email; status and roles have separate validation/permission routes.
5. There are no ticket history or technician deployment semantics yet; their eventual immutability/effective-date rules need approved requirements.
6. Tests use synthetic identities/locations and a separate SQLite in-memory database.

## Regional configuration model

The schema supports one or more campuses without hardcoding CUT geography. Each campus may have many operational zones; buildings belong to a campus and may optionally be assigned to a zone in that same campus; each building may have many rooms. No campus, zone, building, or room seed data is fabricated. An unknown location can be represented in a future workflow but is not an implemented ticket-routing state.

## Decisions still open

Confirm approved campus/zone/location identifiers and configuration owners; exact role titles and grants; whether account access should be campus/zone-scoped; team membership vs temporary deployment semantics and overlap rules; student/staff identity source and account recovery; ticket requester/walk-in identity rules; categories, priority, status, assignment and transfer rules; SLA calendar and escalation behavior; retention/legal basis, audit access and immutability; attachment handling; asset source of truth; timezone/reporting rules; and production database/backup choices. Do not treat the proposed schema as an institutional approval.
