# Route Reference

Generated from `routes/web.php`. All application routes sit behind the `auth` and `account.active` middleware. Route model authorization is enforced in controllers/policies, not by the route list alone. "Placeholder" means an intentional setup-ready screen with no backing workflow.

## Public

| Method | URI | Name | Notes |
| --- | --- | --- | --- |
| GET | `/` | `root` | Redirects to dashboard or login |
| GET | `/login`, `/logout` (Fortify) | `login`, `logout` | Fortify; rate-limited; no registration/reset |

## Authenticated workspace

| Method | URI | Name | Gate / permission | Status |
| --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | — | Implemented |
| GET | `/account` | `account.index` | — | Implemented |
| GET | `/notifications` | `notifications.index` | — | Implemented (own inbox only) |
| POST | `/notifications/{notification}/open` | `notifications.open` | Ownership rechecked | Implemented |
| GET | `/reports/tickets` | `reports.tickets.index` | `tickets.view_all` | Implemented (aggregate only) |
| GET | `/reports/users`, `/reports/assets`, `/reports/system` | `reports.*.index` | — | Placeholder |
| GET | `/knowledge-base/*` | `knowledge.*.index` | — | Placeholder |

## Tickets

| Method | URI | Name | Notes |
| --- | --- | --- | --- |
| GET | `/tickets` | `tickets.index` | Scoped by policy (global for officers) |
| GET | `/tickets/mine` | `tickets.mine` | Requester's own or technician's assignments |
| GET | `/tickets/overdue` | `tickets.overdue` | Filtered queue; no SLA timers |
| GET/POST | `/tickets/create`, `/tickets` | `tickets.create`, `tickets.store` | `tickets.create` |
| GET | `/tickets/{ticket}/assign` | `tickets.assignment.edit` | Policy checked |
| GET | `/tickets/{ticket}` | `tickets.show` | Policy checked |
| POST | `/tickets/{ticket}/comments` | `tickets.comments.store` | Public/internal separation |
| PATCH | `/tickets/{ticket}/assignment` | `tickets.assignment.update` | Officer/assignment policy |
| PATCH | `/tickets/{ticket}/status` | `tickets.status.update` | Fixed lifecycle |
| POST | `/tickets/{ticket}/reopen` | `tickets.reopen` | Explicit operation |

## Administration

| Prefix | Controller | Gate / permission | Status |
| --- | --- | --- | --- |
| `admin/ticket-catalogues/*` | `TicketCatalogueController` | `tickets.catalogues.manage` | Implemented |
| `admin/users/*` | `UserController`, `UserRoleController` | Admin rules | Implemented (no destroy route) |
| `admin/roles` | `RoleController` | Admin rules | Read-only catalogue |
| `admin/user-groups/*` | `UserGroupController` | `user_groups.manage` | Implemented |
| `admin/campuses`, `zones`, `buildings`, `rooms` | Resource controllers | Admin rules | Implemented (no show/destroy) |
| `admin/equipment`, `admin/departments` | Controllers | Admin rules | Setup-ready |
| `admin/maintenance`, `asset-categories`, `settings/*` | `AdminPlaceholderController` | — | Placeholder |
| `admin/audit` | `AuditEventController` | Admin rules | Implemented (filters) |
| `admin/teams`, `team-memberships`, `technician-deployments` | Resource controllers | `teams.manage` | Implemented (no show/destroy) |

## Notes

- Route names are the stable reference for tests and UI links; do not rely on raw URIs.
- Adding a route requires a matching authorization check and a feature test. See [Development guidelines](DEVELOPMENT_GUIDELINES.md) and [Testing strategy](TESTING_STRATEGY.md).
- Placeholders are deliberate; do not treat them as implemented features. See [Project scope](PROJECT_SCOPE.md).

Related: [Database schema](DATABASE_SCHEMA.md) · [Roles and permissions](ROLES_AND_PERMISSIONS.md)
