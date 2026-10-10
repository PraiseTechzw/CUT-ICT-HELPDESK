# Roles and Permissions

**Status:** Implemented proposal catalogue for Phase 1 administrative screens. These names and grants are not confirmed CUT job titles or institutional policy. Review and approve them before real accounts are provisioned.

## Centralized role catalogue

`App\Support\Roles` defines these stable slugs:

| Slug | Proposed label | Seeded Phase 1 grants |
|---|---|---|
| `requester` | Requester | View campuses, zones, buildings, and rooms. No ticket permission exists yet. |
| `helpdesk-officer` | Helpdesk Officer | Same Phase 1 location-view grants; walk-in registration workflows are not implemented. |
| `technician` | Technician | Same Phase 1 location-view grants; no assignments or technician-specific record access exists yet. |
| `regional-supervisor` | Regional Supervisor | Same Phase 1 location-view grants; no scoped regional queue is implemented. |
| `ict-manager` | ICT Manager | Location-view grants, `users.view`, and `audit.view`. |
| `system-administrator` | System Administrator | Every current Phase 1 administrative permission. This role does not confer ticket access because ticket capabilities do not exist. |
| `auditor` | Auditor | `audit.view` only. |

The labels are descriptive proposals; the implementation deliberately does not seed any people or privileged default accounts. The database seeder creates/updates role and permission reference records only. Users created through administration receive the proposed Requester role; authorized administrators can later assign or revoke roles, including revoking all roles from another account. Last-active-administrator and self-change safeguards remain in place.

## Centralized permission catalogue

`App\Support\Permissions` defines granular abilities:

- `users.view`, `users.create`, `users.edit`, `users.deactivate`, `users.assign_roles`
- `campuses.view`, `campuses.manage`
- `zones.view`, `zones.manage`
- `buildings.view`, `buildings.manage`
- `rooms.view`, `rooms.manage`
- `audit.view`

Laravel Gates register permission abilities; Form Requests, middleware, and controllers authorize all sensitive operations server-side. Views conditionally display links/actions for usability but do not replace server-side checks. Account profile fields, status, and roles use distinct workflows. Accounts cannot change their own roles or deactivate themselves.

## Regional access rules in this phase

Campus/location administration is controlled by global Phase 1 permissions. There is no user-to-zone relationship, scoped role assignment, regional supervisor queue, ticket authorization, or cross-zone delegation yet. A location being visible to a role does not grant access to future ticket content. Ticket, history, internal-note, attachment, report, and export policies must be designed and tested in the later phase that creates them.

## Governance responsibilities requiring confirmation

CUT ICT must approve role titles/grants, account lifecycle ownership, who may create/deactivate accounts or assign privileged roles, whether role assignments need campus/zone scope, auditor mandate, cross-zone delegation, password recovery, and emergency administrator recovery. Until approved, treat the seed catalogue as a technical baseline, not permission to give real users those rights.
