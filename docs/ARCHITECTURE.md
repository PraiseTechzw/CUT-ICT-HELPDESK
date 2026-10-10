# Architecture

**Status:** Proposed for review. No application framework has been installed or verified in this repository.

## Approach

Use a **Laravel modular monolith**: one deployable application and relational database, organized around Laravel conventions and cohesive feature responsibilities. This fits the current requirements without introducing distributed-system overhead. Revisit only when verified scale, integration, security, or independent deployment requirements justify a change.

## Application boundaries

- **Identity and access:** authenticated identities, user lifecycle, roles/permissions, and policy checks; identity-provider integration only after approval.
- **Ticketing:** ticket lifecycle, requester, category/priority references, current status, and status history.
- **Operations:** campuses, locations, zones, teams, memberships, and effective-dated technician deployments.
- **Assignment and escalation:** assignment/transfer events, routing decisions, service targets and escalation rules once confirmed.
- **Communications:** requester-visible messages, private internal notes, notifications, and delivery status.
- **Assets and knowledge:** later-stage inventory/maintenance and curated knowledge content.
- **Reporting and audit:** authorized operational summaries and append-oriented audit events, avoiding sensitive data in logs.

These are responsibility boundaries, not a requirement to create a custom framework, separate services, repositories, or namespaces before there is a concrete need. Prefer Laravel's `app/Models`, `app/Http`, `app/Policies`, `app/Notifications`, `routes`, `database`, `resources`, and `tests` conventions; split domain folders only when they improve cohesion.

## Request and data flow (target)

1. An HTTP route dispatches to a focused controller.
2. Authentication middleware establishes the actor; middleware and policy/Form Request authorization deny unauthorized access by default.
3. A Form Request validates input and its authorization rules or a policy enforces access.
4. A controller coordinates an application action/service only where a meaningful multi-step workflow exists.
5. A transaction persists the ticket change and its history/event records together where atomic consistency is required.
6. Notifications are queued or dispatched after committed state; retries and duplicate delivery behavior are designed explicitly.
7. A response exposes only fields the actor is allowed to see. Internal notes are never serialized to requester-facing views.

## Data and operations

Use relational foreign keys, unique constraints and indexes appropriate to actual query paths. Model zone membership and temporary deployments with effective dates; never equate a technician's normal assignment with a temporary deployment. Retain historical assignments/statuses rather than rewriting old tickets when organizational structures change. Support an unknown or unassigned location safely. Details are in [Domain Model](DOMAIN_MODEL.md).

## Security boundaries

Authorization is server-side and applies to every read and write, including regional data isolation. Separate public requester communication from internal notes. Validate uploads and store them privately with authorization-controlled access. Protect state-changing requests with framework CSRF mechanisms, rate-limit sensitive endpoints, avoid logging secrets or unnecessary personal data, and audit important administrative/ticket actions. Production secrets stay outside Git. University SSO, email credentials, and internal APIs require verified requirements and explicit ICT authorization.

## Architectural decisions and rationale

| Decision | Provisional choice | Rationale / revisit trigger |
|---|---|---|
| Deployment shape | Laravel modular monolith | Simple operations and strong Laravel conventions; revisit for demonstrated independent scaling/deployment needs. |
| Frontend | Evaluate Blade/components first | Reduces duplication and operational complexity; revisit if verified interaction needs justify a separate client. |
| Persistence | One SQL database, prefer MySQL/MariaDB | Relational ticket/history/organization data; verify institutional support before selecting version. |
| Domain organization | Cohesive modules using standard Laravel structure | Clear ownership without premature architecture. |
| Organization configuration | Effective-dated relational records | Supports temporary deployments, historical integrity, and configuration change. |
| Integrations | Defer | Require authorization, interface documentation, test access, and operational ownership. |

## Current state

The repository is a clean initial commit with only `.gitignore`, `LICENSE`, and a placeholder README. There is no `artisan`, `composer.json`, frontend manifest, migration, or test harness. PHP and Composer are not installed in this environment, so no application bootstrap or runtime check was possible.
