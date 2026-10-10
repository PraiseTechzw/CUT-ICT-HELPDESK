# Project Scope

**Status:** Phase 0 proposal, based on the user-provided brief. Operational details are unverified; see [Requirements and Open Questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md).

## Problem statement

CUT's ICT helpdesk currently includes a physical walk-in support service. The project intends to complement that service with a digital system that lets authorized personnel register, coordinate, track, resolve, and report on ICT support requests centrally. The existing workflows, volumes, service commitments, and supporting systems have not yet been confirmed.

## Objectives

1. Provide a reliable record of support requests from students, employees, and other authorized requesters, including walk-in requests entered by helpdesk staff.
2. Support configurable campus locations, operational zones, teams, technician deployments, assignments, and escalation without embedding unconfirmed organization details in code.
3. Give requesters and authorized staff appropriate visibility into status and communication.
4. Preserve ticket, assignment, and status history as people and organizational configurations change.
5. Provide a secure, maintainable, testable base for later reporting, knowledge, and asset capabilities.

## Stakeholders and user groups

- Requesters: students, university employees, and other authorized people.
- Walk-in helpdesk staff who register requests on a requester's behalf.
- ICT technicians deployed across operational zones.
- Zone or regional supervisors.
- Specialist ICT teams serving one or more zones.
- Central ICT management.
- System administrators and authorized auditors.
- CUT ICT leadership and institutional technology/security stakeholders (specific approvers to be confirmed).

## Proposed capabilities (not implemented)

Ticket registration and categorization; requester-visible status and communication; assignment and transfer history; technician work logs and internal notes; configurable locations, zones, teams, and temporary deployments; escalation; resolution and feedback; notifications; reporting and audit; knowledge articles; and equipment/maintenance records. The service catalogue and operational rules require ICT confirmation.

## Scope boundaries for Phase 0

Included: inspect the repository, capture the source brief, document architecture and proposed domain model, identify risks and unanswered questions, record setup and testing strategy, and plan later implementation. The repository currently has no application code.

Excluded: implementing the helpdesk, creating the full database schema, inventing real zones or service policies, populating production-like records, implementing SSO or internal integrations, configuring production credentials, deployment, and production infrastructure changes.

## Assumptions and exclusions

- Laravel/PHP and SQL are the preferred direction, not proof of a currently provisioned runtime.
- A modular monolith is the provisional architecture.
- MySQL/MariaDB is preferred unless institutional constraints establish another SQL database.
- Blade with reusable components should be evaluated before a separate frontend.
- CUT-approved logo, palette, slogan, accessibility, hosting, privacy, retention, backup, and security standards must be obtained rather than guessed.
- This phase does not define service-level targets, escalation contacts, identity provider, operational boundaries, or legally mandated data retention.

## Branding and interface constraints

The initial repository inspection found no approved CUT identity assets. Obtain the official logo, palette, slogan, and brand guidance from the university before interface design; do not invent them. When approved, keep branding configuration and reusable design tokens centralized rather than scattering guessed colour values or repeated branding markup. The eventual interface must be accessible, responsive, and suitable for university staff and students, with the applicable accessibility standard confirmed by CUT.
