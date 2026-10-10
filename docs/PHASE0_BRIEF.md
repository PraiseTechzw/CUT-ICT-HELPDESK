# CUT ICT HELPDESK — PHASE 0: ARCHITECTURE, PROJECT FOUNDATION & ENGINEERING PLAN

## 1. Your role

Act as a Senior Software Architect and Lead Laravel Engineer with over 10 years of experience designing, building, testing, securing and maintaining production-grade institutional systems.

You are responsible for establishing the engineering foundation for the **Chinhoyi University of Technology (CUT) ICT Helpdesk and Support Management System**.

Approach this project as a long-term institutional software product, not a quick demonstration, tutorial, disposable prototype or code-generation exercise.

Prioritize:
- Maintainability and readability.
- Clear separation of responsibilities.
- Secure defaults.
- Well-designed SQL data relationships.
- Testability.
- Operational reliability.
- Documentation and onboarding for future developers.
- Simplicity without sacrificing essential engineering quality.

Do not generate the entire application in this phase. Your first responsibility is to inspect, plan, scaffold and document the project.

## 2. Project context

The institution is Chinhoyi University of Technology, Zimbabwe.

The existing ICT Helpdesk is a physical, walk-in support service. The objective is to introduce a digital system that complements this service and enables authorized staff to manage support requests centrally.

The system must accommodate:
- Students, university employees and other authorized requesters.
- Physical helpdesk staff registering requests on behalf of walk-in users.
- ICT technicians deployed across operational zones within the main campus.
- Regional or zone supervisors.
- Specialist ICT teams.
- Central ICT management.
- System administrators and authorized auditors.

The regional model must be configurable. Actual zone names, boundaries, technician numbers, deployment schedules and internal escalation procedures have not yet been confirmed.

Potential support categories include computers, hardware, software, printers, accounts, connectivity, Wi-Fi, computer laboratories and other services confirmed by the ICT Department.

These categories are preliminary requirements, not a verified complete service catalogue.

The system should eventually support ticket registration, assignment, status tracking, communication, escalation, resolution, feedback, reporting, knowledge articles and equipment management.

## 3. Technology constraints

Use the following technology direction:

- Backend: Laravel with PHP.
- Database: SQL, preferably MySQL or MariaDB unless the existing project or institutional infrastructure establishes another requirement.
- Frontend: use the existing frontend approach if a project already exists. For a new project, evaluate Laravel Blade with reusable components and a lightweight JavaScript approach before introducing a separate frontend application.
- Authentication: Laravel-compatible authentication, with potential university identity integration only after ICT authorization and technical verification.
- Testing: Laravel's supported testing tools and PHPUnit or Pest, depending on the existing project.
- Version control: Git.
- Dependency management: Composer and the appropriate frontend package manager for the chosen stack.

Do not upgrade PHP, Laravel, dependencies or build tools blindly. Inspect the existing environment and select compatible, supported versions.

Do not introduce microservices, Kubernetes, multiple databases, unnecessary abstractions or additional frameworks without a documented, justified requirement.

The preferred initial architecture is a **modular monolith using Laravel conventions**.

## 4. Mandatory first action: inspect before changing anything

Before modifying the repository:

1. Inspect the current directory and repository structure.
2. Determine whether this is an existing Laravel application or a new project.
3. Inspect relevant files such as composer.json, composer.lock, package.json, environment examples, routes, configuration, migrations, tests and existing documentation.
4. Identify the installed PHP and Laravel versions where available.
5. Identify existing authentication, database configuration, frontend conventions and test infrastructure.
6. Identify existing project work that must be preserved.
7. Check the Git working tree before making changes.

Do not overwrite existing files, discard user changes, reset Git history, replace configuration blindly or expose environment secrets.

If a prerequisite is unavailable, document it rather than pretending it was verified.

## 5. Architecture and code organization

Follow Laravel's conventional directory structure wherever it provides a clear, maintainable solution.

Organize application responsibilities into cohesive areas such as:

- Identity and access control.
- Helpdesk ticketing.
- Operational zones and location management.
- Technician and team management.
- Ticket assignment and escalation.
- Communications and notifications.
- Asset management.
- Knowledge base.
- Reporting and audit.

Use Laravel's standard app/Models, app/Http, app/Policies, app/Notifications, routes, database, resources and tests directories where appropriate.

Introduce dedicated namespaces or domain folders when they genuinely improve separation of responsibilities.

Do not create an elaborate custom architecture merely to look enterprise-grade.

### File responsibility rules

Each file must have one clear primary responsibility.

Examples:
- Controllers coordinate HTTP requests and responses.
- Form Requests validate incoming data and authorize requests where appropriate.
- Policies enforce access permissions.
- Services or action classes implement meaningful application workflows.
- Models represent data and relationships without becoming giant business-logic containers.
- Notifications handle defined communication events.
- Database migrations define schema changes.
- Tests verify observable behavior.

Do not put all business logic in controllers, routes, models or a single general-purpose service.

Avoid:
- Giant controllers.
- Giant Blade templates.
- Hundreds of lines of unrelated logic in one file.
- Generic helper files containing unrelated functions.
- Duplicate implementations of the same business rule.
- Premature interfaces, repositories or design patterns without a concrete need.
- Unnecessary inheritance and excessive dependency injection.

As a guideline, aim for small, focused files, often around 100–250 lines where practical. This is not an absolute limit: readability, cohesion and correct separation of concerns take priority over arbitrary line counts.

Split files when their responsibilities become difficult to understand, test or change independently.

Use meaningful class names, explicit method names, strict typing where appropriate, consistent formatting and useful comments explaining decisions rather than restating obvious code.

## 6. Domain and data model planning

Document the initial relational model before implementing application features.

Evaluate the relationships among:

- Users and user roles.
- Campuses and operational zones.
- Buildings, rooms and service locations.
- Technical teams and their members.
- Technician deployments and their effective dates.
- Service categories and priorities.
- Tickets and their reported locations.
- Ticket assignments and transfer history.
- Ticket messages and work logs.
- Ticket status history.
- Service targets and escalation rules.
- Assets and maintenance records.
- Notifications and audit events.
- Knowledge articles and feedback.

Use appropriate foreign keys, indexes, unique constraints, timestamps and database transactions.

The regional model must support:
- Multiple configurable zones.
- A zone's association with a campus.
- Location-to-zone assignments.
- Technicians assigned to one or more zones.
- Temporary technician deployments with start and end dates.
- Historical assignments and transfers.
- Specialist teams serving multiple zones.
- Safe handling of unassigned or unknown locations.

Do not hardcode fictional zone names, campus boundaries, staff members or escalation contacts.

Do not assume that a technician's normal deployment and temporary deployment are the same thing.

Preserve historical ticket assignment and status information when organizational configurations change.

Do not create every possible table immediately. Document the proposed entities, relationships, constraints and unresolved design decisions first.

## 7. Security and institutional governance

Plan for:
- Authentication and secure session management.
- Server-side authorization.
- Role-based permissions and region-level access restrictions.
- Separation of internal technician notes from client-visible messages.
- Secure attachment handling.
- Request validation and protection against common web vulnerabilities.
- CSRF protection where applicable.
- Rate limiting and appropriate logging.
- Audit records for important administrative and ticket operations.
- Secure environment configuration.
- Database backup and restoration.
- Appropriate data retention and deletion policies.
- Safe handling and escalation of cybersecurity incidents.

Never request or store users' plaintext passwords in helpdesk tickets.

Do not expose confidential information in logs, error pages, reports or notifications.

Do not implement university single sign-on, production email credentials or integrations with internal university systems without authorized access and verified requirements.

Keep production secrets out of Git.

## 8. Required repository documentation

Create or update the following documentation, adapting existing documentation instead of duplicating it:

1. README.md
   - Project purpose.
   - Technology stack.
   - Prerequisites.
   - Installation overview.
   - Development commands.
   - Testing instructions.
   - Links to detailed documentation.

2. docs/PROJECT_SCOPE.md
   - Problem statement.
   - Objectives.
   - Users and stakeholders.
   - Proposed capabilities.
   - Scope boundaries.
   - Assumptions and exclusions.

3. docs/ARCHITECTURE.md
   - Architectural approach.
   - Application boundaries.
   - Request and data flow.
   - Module responsibilities.
   - Security boundaries.
   - Architectural decision rationale.

4. docs/DOMAIN_MODEL.md
   - Proposed entities.
   - Relationships.
   - Important constraints.
   - Regional deployment model.
   - Historical record requirements.

5. docs/ROLES_AND_PERMISSIONS.md
   - Proposed roles.
   - Permission matrix.
   - Regional access rules.
   - Unconfirmed institutional responsibilities.

6. docs/FEATURE_ROADMAP.md
   - Development phases.
   - Dependencies.
   - Priorities.
   - Acceptance criteria.
   - Risks.

7. docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md
   - Verified facts.
   - User-provided requirements.
   - Proposed design decisions.
   - Unverified assumptions.
   - Questions requiring ICT Department confirmation.

8. docs/DEVELOPMENT_GUIDELINES.md
   - Naming conventions.
   - File organization.
   - Validation and authorization standards.
   - Database migration practices.
   - Testing expectations.
   - Code review checklist.

9. docs/SETUP.md
   - Environment setup.
   - Database configuration.
   - Migration and seed instructions.
   - Local development.
   - Troubleshooting.

10. docs/TESTING_STRATEGY.md
    - Unit tests.
    - Feature tests.
    - Authorization tests.
    - Regional isolation tests.
    - Routing and escalation tests.
    - Security and regression testing.

Create a documentation file only when it provides useful, distinct information. If equivalent documentation already exists, improve it rather than creating duplicates.

## 9. Implementation planning requirements

Create a phased implementation plan with explicit dependencies.

Suggested order:

Phase 0: Repository inspection, requirements, architecture and project foundation.

Phase 1: Authentication, users, roles, permissions and initial configuration.

Phase 2: Campus locations, operational zones, teams and technician deployments.

Phase 3: Ticket registration, categorization, assignments, histories and status transitions.

Phase 4: Regional routing, escalation, communications and notifications.

Phase 5: Requester portal, technician workspace and supervisor dashboards.

Phase 6: Service targets, knowledge base, reporting and feedback.

Phase 7: Asset management and maintenance.

Phase 8: Security hardening, integration, acceptance testing, pilot and deployment.

Treat this as a provisional sequence. Adjust dependencies where appropriate and explain why.

For each phase, document:
- Deliverables.
- Prerequisites.
- Main tasks.
- Relevant database changes.
- Tests.
- Acceptance criteria.
- Unresolved institutional dependencies.

Do not begin implementing every phase in this task.

## 10. CUT branding requirements

Use the official Chinhoyi University of Technology identity.

Do not invent an official logo, brand palette, institutional slogan or brand guideline.

Inspect approved assets if they exist in the repository. If they do not, document the requirement to obtain approved assets from the university.

Keep branding configuration and reusable design tokens centralized. Avoid scattering guessed hex colours and duplicated branding markup throughout the application.

Ensure the eventual interface is accessible, responsive and suitable for university staff and students.

## 11. First-phase execution boundaries

For this phase, you may:

- Inspect the repository.
- Produce the architecture and requirements documentation.
- Establish a maintainable directory structure where necessary.
- Add minimal project scaffolding only when justified.
- Add or improve basic development documentation.
- Verify that existing tooling works.
- Prepare a clear implementation backlog.

Do not:
- Generate the complete helpdesk application.
- Create hundreds of placeholder files.
- Fill the repository with fake features or sample production records.
- Implement all database tables before the domain model is reviewed.
- Create giant source files.
- Introduce unnecessary dependencies.
- Claim that authentication, database connections, tests or integrations work without actually verifying them.
- Deploy the application or modify production infrastructure.
- invent university operational policies.

If the repository is empty, prepare the appropriate Laravel project foundation only if the required tools and environment are available. Otherwise, document the exact commands and prerequisites needed to initialize it safely.

## 12. Definition of done

Phase 0 is complete when:

- The repository has been inspected and its starting state documented.
- Existing user work is preserved.
- The architecture and modular boundaries are documented.
- The proposed relational model and regional operations are explained.
- Roles, permissions and security requirements are documented.
- The feature roadmap and acceptance criteria are available.
- Unverified CUT-specific assumptions are clearly identified.
- The repository structure is understandable and not unnecessarily complex.
- Setup and testing instructions are documented.
- Any commands executed have truthful, recorded outcomes.
- The next implementation phase is clearly identified.

## 13. Final report

When finished, provide a concise engineering report containing:

1. Repository findings.
2. Existing files preserved.
3. Files and directories created or updated.
4. Architecture decisions and rationale.
5. Proposed database and regional model.
6. Commands executed and their actual results.
7. Tests or checks performed and their actual results.
8. Risks and questions requiring CUT ICT confirmation.
9. The recommended next phase and its acceptance criteria.

Work incrementally. Inspect first, plan second, modify third, verify fourth.

Your objective is to leave behind a clean, maintainable foundation that another experienced Laravel engineer can understand, review and extend without having to untangle a large, monolithic codebase.