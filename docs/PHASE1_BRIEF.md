# CUT ICT HELPDESK — PHASE 1
## Authentication, Authorization, User Administration and Regional Configuration

### 1. Role and engineering standards

Act as the senior Laravel engineer responsible for implementing Phase 1 of the Chinhoyi University of Technology ICT Helpdesk.

Phase 0 has already established the project plan, repository structure and architecture documentation.

Your responsibility is to implement a small, complete, maintainable and tested foundation that follows those approved architectural decisions.

Work as an experienced production software engineer. Inspect before modifying, reuse existing conventions, implement incrementally, verify your changes, and document your results.

Do not rewrite the architecture unnecessarily or regenerate the project from scratch.

### 2. Mandatory pre-implementation inspection

Before writing code:

1. Read the existing README and relevant files under docs/.
2. Inspect composer.json, composer.lock, routes, models, migrations, authentication, authorization, frontend resources and tests.
3. Determine the installed Laravel and PHP versions.
4. Identify the current SQL database configuration and testing database strategy.
5. Review the existing Git working tree and preserve unrelated changes.
6. Identify existing authentication packages or starter kits.
7. Compare the implementation plan with the current repository.

Do not expose .env contents, credentials, session secrets or other sensitive information in terminal output or documentation.

If an existing implementation already satisfies a requirement, reuse and test it instead of creating a duplicate.

If a significant contradiction exists between the approved documentation and the repository, document it and choose the safest compatible approach.

### 3. Scope of this phase

Implement only the following capabilities:

A. Authentication.
B. User profile and account administration.
C. Roles and permissions.
D. Campus administration.
E. Operational zone administration.
F. Building and room administration.
G. The foundational relationships between users, teams, zones and locations where justified by the approved domain model.
H. Automated tests, security checks and updated documentation.

Do not implement the complete ticketing system, technician assignment engine, asset management, knowledge base, advanced reporting, or production integrations in this phase.

Prepare a sound foundation for these later modules without prematurely implementing them.

### 4. Authentication

Implement authentication using a maintained, Laravel-compatible solution appropriate to the installed version.

Required capabilities:

- Login and logout.
- Secure password hashing.
- Session regeneration after authentication.
- Session invalidation and CSRF token regeneration on logout, following Laravel conventions.
- Protected routes.
- Server-side validation and meaningful validation errors.
- Appropriate login throttling.
- Account status checks.
- Secure password reset only if the configured mail or reset-token workflow can be implemented and tested properly.

Do not invent university single sign-on, LDAP, Active Directory or institutional identity integration.

If the repository already contains a suitable authentication implementation, inspect and extend it instead of installing a competing package.

Keep authentication separate from authorization. Being authenticated must not automatically grant administrative access.

### 5. User and account administration

Implement the user model and administrative workflows needed for this phase.

Authorized administrators should be able to:

- List and search accounts.
- View account details.
- Create accounts.
- Edit permitted profile fields.
- Activate or deactivate accounts.
- Assign and revoke authorized roles.
- View relevant account status and timestamps.

Apply pagination and server-side validation.

Protect privileged account operations against unauthorized access and accidental privilege escalation.

Ordinary users must not be able to change their own role, grant themselves administrative privileges, or modify another user's protected account fields.

Avoid collecting unnecessary personal information.

Do not create a public registration workflow unless the approved requirements explicitly authorize it.

Prepare for future student and staff identity integration without assuming that a university identity provider is available.

### 6. Roles and permissions

Use Laravel Gates and Policies, or an existing approved permission package if one is already installed and appropriate.

Implement the following proposed role categories, adjusting names only when required by the repository's established conventions:

- Requester.
- Helpdesk Officer.
- Technician.
- Regional Supervisor.
- ICT Manager.
- System Administrator.
- Auditor, if required by the approved requirements.

Keep role definitions centralized and documented.

Use granular permissions for administrative operations rather than scattering hardcoded role-name checks throughout controllers and views.

For example, distinguish permissions to:

- View users.
- Create users.
- Edit users.
- Deactivate users.
- Assign roles.
- View campus locations.
- Manage campuses.
- Manage operational zones.
- Manage buildings and rooms.
- View audit records.

Do not assume that all proposed roles have been formally approved by CUT.

Implement only the minimum roles and permissions required for the current functionality, while documenting how later roles will be added.

If a permission package is not needed, do not introduce one solely for appearance.

### 7. Regional and location administration

Implement configurable university location data.

#### Campus

Provide administrative management for campus records with fields such as:

- Name.
- Unique code.
- Description, where appropriate.
- Active/inactive status.
- Timestamps.

#### Operational zone

Provide administrative management for zones within a campus.

Include:

- Parent campus.
- Name.
- Unique code within the agreed scope.
- Description.
- Active/inactive status.
- Timestamps.

The initial deployment is based on operational zones within CUT's main campus. However, do not hardcode the application to a single campus or assume unverified region names.

Use clearly marked development seed data only when required for tests or local demonstration.

#### Buildings and rooms

Implement buildings belonging to campuses and rooms belonging to buildings, provided this is consistent with the approved Phase 0 domain model.

Allow authorized users to manage locations.

Validate parent-child relationships and ensure that a room cannot be associated with a building from an unrelated campus.

If zones are assigned at building or room level, use the documented location-assignment model rather than duplicating free-text zone names across records.

The precise zone boundaries remain configurable and must not be fabricated.

#### Operational safety

- Use foreign keys and appropriate indexes.
- Apply uniqueness constraints for agreed codes.
- Validate all inputs on the server.
- Prevent deletion of referenced locations where deletion would damage data integrity.
- Prefer deactivation when historical records must be retained.
- Use transactions for multi-step administrative changes.
- Ensure all management actions require appropriate authorization.

Do not implement geographic maps, GPS tracking or live technician tracking in this phase.

### 8. Teams and user-zone relationships

Review docs/DOMAIN_MODEL.md before implementing these relationships.

If the approved design establishes teams and technician deployments as foundational entities, implement only their basic schema and administration needed for this phase.

Support the possibility that a technician may serve more than one zone.

Do not assume that a user's home zone is identical to a temporary deployment.

Do not invent technician names, actual staffing allocations, deployment schedules or operational boundaries.

If these relationships are scheduled for a later phase, document their intended integration and avoid adding premature tables.

### 9. Database and migration standards

Use Laravel migrations and Eloquent relationships.

Requirements:

- Explicit foreign keys.
- Appropriate unique constraints and indexes.
- Consistent naming.
- Appropriate deletion behavior.
- Timestamps where useful.
- Database transactions where operations span multiple writes.
- Reversible migrations where reasonably possible.
- No destructive schema changes without a documented justification.
- No modification of production data.

Do not edit old migrations that may already have been applied unless the repository's state and migration strategy explicitly justify doing so. Prefer new migrations for subsequent schema changes.

Use factories and isolated seeders for tests and local development.

Never seed fabricated production users or privileged default accounts.

If development administrator bootstrap is required, provide a secure, documented method appropriate to the current environment. Never hardcode a shared production password.

### 10. User interface

Follow the frontend stack and design conventions established in Phase 0.

Implement only the screens needed to verify Phase 1:

1. Login.
2. Account listing.
3. Account creation and editing.
4. Role management or role assignment.
5. Campus listing and form.
6. Operational zone listing and form.
7. Building and room management if included in the domain model.
8. Clear access-denied and validation-error states.

Use reusable components for common interface patterns.

Keep controllers, validation, authorization and business logic out of large templates.

Do not build a full ticketing dashboard yet.

Follow the official CUT branding requirements documented in Phase 0. Use approved assets when available. Do not invent an official logo or claim unverified colour codes are official.

Ensure forms have labels, keyboard accessibility, clear validation feedback and responsive layouts.

### 11. Automated testing requirements

Write automated tests using the repository's existing Laravel-compatible test framework.

At minimum, verify:

#### Authentication

- Guests cannot access protected administrative routes.
- Valid credentials allow login.
- Invalid credentials do not authenticate.
- Logout invalidates the authenticated session.
- Inactive accounts cannot use protected services.
- Authentication throttling works where configured.

#### Authorization

- Requesters cannot manage user accounts.
- Ordinary users cannot grant themselves elevated roles.
- Unauthorized users cannot create or edit campuses.
- Unauthorized users cannot create or edit operational zones.
- Only authorized administrators can assign privileged roles.
- Direct URL access cannot bypass permission checks.
- Unauthorized access returns an appropriate response without leaking restricted data.

#### Location integrity

- A zone must reference a valid campus.
- Building and room relationships are valid.
- Duplicate codes are rejected according to the documented uniqueness rules.
- Referenced locations cannot be deleted in a way that breaks data integrity.
- Inactive locations cannot be used inappropriately by future operational workflows.

#### User administration

- Account creation and validation work.
- Account updates only change permitted fields.
- Unauthorized role changes are rejected.
- Deactivated accounts are handled correctly.
- Pagination and search work where implemented.

#### Regression and integration

- Existing tests continue to pass.
- Database migrations work in a fresh test database.
- The authentication and administration screens load successfully.
- Database constraints agree with application validation.

Tests must use isolated data and a dedicated testing database. Never run destructive tests against a production database.

Do not mark a test as passed without executing it and checking its result.

### 12. Code quality and verification

After implementation:

1. Run the appropriate test suite.
2. Run Laravel's available route and migration checks.
3. Verify migrations on a fresh, disposable test database.
4. Run available formatting, static analysis and dependency checks if configured.
5. Inspect changed files for duplicated logic and unnecessary abstractions.
6. Verify authorization on routes, controller actions and relevant data access.
7. Confirm that existing unrelated files and changes remain intact.
8. Document checks that could not be run and explain why.

Do not run destructive database refresh commands against an environment whose purpose is uncertain.

Fix failures caused by your changes. Clearly distinguish pre-existing failures from newly introduced ones.

### 13. Documentation updates

Update the existing documentation to reflect the implementation:

- README.md.
- docs/ARCHITECTURE.md.
- docs/DOMAIN_MODEL.md.
- docs/ROLES_AND_PERMISSIONS.md.
- docs/SETUP.md.
- docs/TESTING_STRATEGY.md.
- docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md.

Add documentation for new environment variables, local administrator bootstrap and setup requirements as needed.

Do not create duplicate documents where an existing file already covers the subject.

Record unverified institutional decisions rather than presenting assumptions as facts.

### 14. Definition of done

Phase 1 is complete only when:

- Authentication works.
- Protected routes enforce authorization.
- User administration works within defined permissions.
- Campus and operational zone administration work.
- Building and room management work if included in the approved scope.
- Database relationships and constraints are valid.
- Automated tests cover critical workflows and security boundaries.
- The existing application remains intact.
- Setup and testing documentation is current.
- Actual verification results are recorded.
- Outstanding institutional decisions are documented.

### 15. Final engineering report

When finished, report:

1. What existed before implementation.
2. What was implemented.
3. Which files were created or modified.
4. Which database migrations were added.
5. The implemented role and permission model.
6. How campus and zone configuration works.
7. Tests executed and their actual outcomes.
8. Formatting, static analysis and other checks executed.
9. Security issues or remaining risks.
10. Decisions still requiring CUT ICT approval.
11. The recommended next phase.

Do not claim production readiness merely because local tests pass.

Stop after Phase 1. Do not proceed to the full ticketing system until the foundation has been reviewed and verified.