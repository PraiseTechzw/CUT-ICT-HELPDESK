# Glossary

Terms with a specific meaning in this project. Where a term is still provisional, that is stated. See [Domain model](DOMAIN_MODEL.md) and [Roles and permissions](ROLES_AND_PERMISSIONS.md).

| Term | Meaning |
| --- | --- |
| **Requester** | Proposed role for a person reporting an issue. Sees only their own tickets. |
| **Helpdesk Officer** | Proposed role with global triage and assignment (`tickets.view_all`). |
| **Technician** | Proposed role that works only tickets individually assigned to them. |
| **System Administrator** | Proposed role currently receiving all defined permissions; needs CUT approval for production. |
| **Triage** | Reviewing incoming tickets and assigning an active individual Technician. |
| **Catalogue** | Configurable category/priority list. Empty by default; no CUT values invented. |
| **Internal note** | Technician/Officer comment hidden from requesters; separate permission from public reply. |
| **Public reply** | Comment visible to the requester. |
| **Reopen** | Explicit operation returning a resolved/closed ticket to the Open queue. |
| **Operational zone** | Administrative location tier below campus and above building. Provisional. |
| **Team** | Generic administrative group of Technicians. No ticket routing or authorization effect. |
| **Membership** | Inclusive-dated record associating a Technician with a Team. |
| **Deployment** | Date-bounded record associating a Technician with an Operational Zone. Records only, not a schedule or access grant. |
| **Provisional** | Implemented locally but not approved as CUT institutional policy. |
| **Placeholder / setup-ready portal** | Screen that documents intended future capability without a backing workflow. |
| **Audit event** | Administrative change record with secrets and changed text omitted. |
| **Notification** | Database-only in-app message with generic payload (ticket ID, event code, generic text). |
| **Schema-only** | Table/column exists in migrations but has no user-facing workflow (e.g. `ticket_attachments`, `due_at`). |

Related: [FAQ](FAQ.md) · [Route reference](ROUTE_REFERENCE.md) · [Database schema](DATABASE_SCHEMA.md)
