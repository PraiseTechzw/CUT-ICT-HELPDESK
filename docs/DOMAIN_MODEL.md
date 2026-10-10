# Proposed Domain Model

**Status:** Conceptual model only; no migrations or tables have been created. Names and constraints must be validated with CUT ICT before implementation.

## Proposed entities and relationships

- **User**: requester or staff identity. User may have multiple **RoleAssignments**; assignment may be scoped to a campus, zone, or team if confirmed. Authentication source is undecided.
- **Role / Permission**: authorization catalogue. Prefer a Laravel-compatible authorization approach; do not assume a package before assessing project needs.
- **Campus** 1—many **OperationalZones**; each zone belongs to a campus. Multiple campuses are supported even if initial deployment is one campus.
- **ServiceLocation** (building/room/other approved place): belongs to a campus and may have a nullable zone reference. Keep unknown/unassigned locations representable; do not force a fabricated zone.
- **TechnicalTeam**: may serve many zones, and a zone may be served by multiple specialist teams, through an effective-dated team-zone association if history is operationally important.
- **TeamMembership**: user/technician and team association with effective dates and optional role-in-team.
- **TechnicianDeployment**: technician, zone, start date, optional end date, and deployment type/source. Separate this temporary/effective-dated deployment from ordinary team membership or home assignment.
- **ServiceCategory** and **Priority**: configurable catalogues. Priority behavior and category hierarchy need confirmation.
- **Ticket**: requester (possibly nullable for a walk-in if a reliable identity is unavailable, subject to policy), created-by staff actor when registered on behalf of someone, category, priority, current status, reported location (possibly unknown), concise subject/description, timestamps, and any approved reference number.
- **TicketAssignment**: ticket, assigned technician/team/zone as applicable, assigning actor, start/end timestamps, transfer reason, and assignment type. Preserve each assignment event; avoid overwriting history.
- **TicketStatusHistory**: ticket, old/new status, actor, timestamp, reason; immutable-by-convention after creation.
- **TicketMessage**: ticket, author, body, created time, visibility (`requester` vs `internal`); enforce visibility at query/serialization/policy boundaries.
- **WorkLog**: ticket, technician, time/event details, visibility rules, created timestamp; confirm whether duration/time billing is needed before storing it.
- **ServiceTarget / EscalationRule**: versioned/configurable target and rule definitions only after service policy is confirmed; each applied deadline/escalation should retain the rule/version snapshot needed for audit.
- **Asset** and **AssetMaintenanceRecord**: optional later-phase inventory and maintenance model, linked to location and/or ticket only where a verified workflow requires it.
- **NotificationRecord**: intended recipient, event, channel, status/timestamps, minimal payload references; do not store secrets or unnecessary message copies.
- **AuditEvent**: actor, action, subject reference, timestamp, and safe contextual metadata. Define retention and access controls with ICT governance.
- **KnowledgeArticle / ArticleFeedback**: author/owner, content, publication state, version history, and requester feedback; scope and moderation policy require confirmation.

## Key integrity rules to evaluate

1. Foreign keys protect relationships; deletion behavior must preserve history (prefer restrict/soft-delete or archival where appropriate rather than cascading away tickets/audits).
2. Add indexes for ticket lookup/status/date, current assignments, location/zone filtering, actor/time history, and queue dashboards after query patterns are known.
3. Use unique constraints for approved stable identifiers (e.g., ticket number, canonical login identifier) only after identifier rules are confirmed.
4. Use database transactions for ticket changes that also append assignment/status/audit history. Define idempotency for repeated integrations/events if introduced.
5. Apply effective-date checks to deployments, membership, and team-zone relationships; decide overlap semantics before enforcing them.
6. Store timestamps consistently (UTC recommended) and render in an agreed local timezone. Confirm timezone/calendar reporting requirements.
7. Capture historical display/reference values where needed so changes to names/catalogues do not rewrite ticket meaning.
8. Treat personal data minimization, retention, archival, export, and erasure as governance decisions; do not silently hard-delete regulated history.

## Regional deployment model

A campus can have configurable zones; locations map to a campus and optionally a zone. Technicians may serve multiple zones and have dated temporary deployments. Specialist teams may support several zones. Ticket assignments and transfers carry their own timestamps and actor/reason so later organizational changes do not alter past responsibility. Unknown, off-campus, or unverified locations need a safe unassigned state pending clarification, not guessed geography.

## Decisions still open

Confirm campus and zone hierarchy, identifiers, room/building catalogue ownership, team membership vs deployment semantics, overlapping deployment rules, whether a ticket may target both team and technician, reassignment approvals, requester identity requirements for walk-ins, category/priority taxonomy, SLA calendars and pauses, attachment needs, record retention, audit immutability, and whether asset records belong in this system or another institutional source of truth.
