# Roles and Permissions

**Status:** Phases 1–4 authorization behavior is implemented and tested. `teams.manage` is a user-approved provisional administration grant; Phase 4 reports reuse `tickets.view_all` and add no permission. Role labels and grants are not confirmed CUT job titles or institutional policy.

## Centralized role catalogue

`App\Support\Roles` defines these slugs:

| Slug | Proposed label | Seeded Phase 2 ticket grants |
|---|---|---|
| `requester` | Requester | Create, view, and publicly reply to own tickets; reopen own resolved/closed ticket. No ticket for another requester is visible. |
| `helpdesk-officer` | Helpdesk Officer | View all tickets globally for triage; manually assign/reassign active Technicians; change allowed ticket status; add public replies and internal notes. |
| `technician` | Technician | View/work only individually assigned tickets; update allowed work statuses; add public replies and internal notes. Cannot assign, close, or reopen. |
| `regional-supervisor` | Regional Supervisor | Phase 1 location-view grants only. No regional ticket queue or ticket-content access is seeded. |
| `ict-manager` | ICT Manager | Location-view grants, `users.view`, and `audit.view`. No ticket-content access is seeded by default. |
| `system-administrator` | System Administrator | Phase 1 administrative permissions plus `tickets.catalogues.manage` and `teams.manage`; can administer metadata but does not receive ticket-content access by default. |
| `auditor` | Auditor | `audit.view` only. No ticket-content access is seeded. |

The user administration flow assigns the proposed Requester role to new accounts. No people or privileged default account are seeded. Role assignment and account status remain separate privileged workflows. The system administrator ticket boundary is intentional: new ticket data permissions are not automatically added to its grants.

## Centralized permission catalogue

In addition to `users.*`, location and `audit.view` abilities, `App\Support\Permissions` defines:

- `tickets.create`, `tickets.view_own`, `tickets.reply_own`, `tickets.reopen_own`
- `tickets.view_all`, `tickets.triage`, `tickets.assign`
- `tickets.view_internal` (read internal notes; separate from comment creation)
- `tickets.view_assigned`, `tickets.work_assigned`
- `tickets.comment_public`, `tickets.comment_internal`
- `tickets.catalogues.manage`
- `teams.manage` (create/update generic teams, technician memberships, and deployments)

The seeder explicitly maps requester/helpdesk/technician permissions and grants System Administrator the separate `teams.manage` metadata ability and catalogue administration. Any future ticket permission must be reviewed and explicitly mapped to roles; it must not be automatically inherited by an administrative role.

Phase 3 grants `teams.manage` only to System Administrator. It authorizes administration of team, membership, and deployment records; it does not grant ticket visibility, routing, assignment, escalation, or zone-based access.

Phase 4 reporting is authorized by the existing `tickets.view_all` ability, so only the seeded Helpdesk Officer role receives the report link/access. It adds no new permission and does not grant report access to System Administrators, ICT Managers, Technicians, Requesters, or Auditors. The aggregate output contains no individual ticket details; the audience remains provisional pending CUT review.

Laravel Gates register permission abilities. `TicketPolicy` applies record-level requester ownership and assigned-technician checks. Routes, Form Requests, query scopes, and controller/service checks enforce access server-side. The views conditionally display links/actions for usability but do not replace authorization.

## Ticket visibility and action rules

- **Requester:** can see only tickets they requested, create their own, add public replies, and explicitly reopen their own resolved/closed ticket. Internal notes and technician identities/assignment history are hidden.
- **Helpdesk Officer:** has global `tickets.view_all` and triage permissions. The queue is not campus/zone-scoped in this MVP. Helpdesk can manually assign one active user with the Technician role; no team-based or automatic ticket assignment exists.
- **Technician:** ticket list queries are restricted to `assigned_technician_id = signed-in user`. Direct access to another or unassigned ticket returns not found. Technicians can work on assigned tickets and add public/internal comments, but cannot assign or close/reopen.
- **System Administrator:** can manage category/priority lists and Phase 3 metadata but has no seeded permission to read ticket content or view reports. A separate, explicitly reviewed grant would be required to confer ticket operational/report access.
- **Regional Supervisor, ICT Manager, Auditor:** receive no ticket-content permissions by default. No scoped regional ticket access is implemented.

Public replies and internal work notes have separate visibility. `tickets.view_internal` is distinct from `tickets.comment_internal`: reading a ticket does not implicitly expose internal notes, and permission to read notes does not itself authorize writing them. The seeded Helpdesk Officer and Technician grants include both, still bounded by triage/assigned-ticket record policy. Audit events omit ticket subjects, descriptions, and comment bodies. Category/priority management does not provide access to ticket content.

## Regional access rules and governance

Location administration is still controlled by global Phase 1 permissions. Tickets are not linked to a campus/zone/room, and there is no user-to-zone relationship, scoped role assignment, regional supervisor queue, or cross-zone delegation. Helpdesk-wide access is global and requires CUT review before real data is used.

CUT ICT must approve role titles/grants, account lifecycle ownership, category/priority catalogue ownership and values, the global helpdesk queue, ticket privacy and retention, who can read internal notes, whether administrators should have ticket access, regional scope/team semantics, auditor mandate, password recovery, and emergency administrator recovery. The seeded roles are a technical proposal, not authority to grant production access.
