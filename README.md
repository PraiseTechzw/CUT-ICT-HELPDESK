# CUT ICT Helpdesk

A digital support-management foundation for Chinhoyi University of Technology (CUT), Zimbabwe, intended to complement the existing walk-in ICT helpdesk. **Phases 1–3 are implemented and locally verified; Phase 3 is a provisional, metadata-only design pending CUT review.** Institutional policies, deployment requirements, and production readiness remain unapproved.

## Current status

- Laravel **13.35.0** with PHP **8.3.6** was observed in the implementation sandbox; Laravel Fortify provides the authentication backend.
- Blade, Tailwind CSS 4, and Vite provide the server-rendered interface.
- SQLite is used for local development and isolated in-memory tests. MySQL/MariaDB and CUT hosting have not been verified.
- Login and logout are implemented; public registration and password reset are disabled. No SSO/LDAP/AD integration is assumed.
- Seven proposed roles and granular permissions are seeded. No default or seeded privileged account is created.
- Account and campus/zone/building/room administration are permission-guarded and audited.
- Requesters can create, view, publicly reply to, and reopen only their own tickets. Helpdesk Officers can triage all tickets and manually assign an active individual Technician. Technicians can view/work only their assigned tickets.
- Public replies and internal work notes are stored separately. Requesters cannot see internal notes or technician identities; reading internal notes has a separate explicit permission from writing them. Audit events never store ticket descriptions or comment bodies.
- The lifecycle is Open → Assigned → In Progress → Waiting on Requester → Resolved → Closed. Reopening is an explicit action that returns the ticket to the unassigned Open queue.
- Category and priority catalogues are configurable, but are deliberately empty initially; no CUT-specific values are invented. The System Administrator grant allows catalogue and Phase 3 metadata administration but does not by itself grant ticket-content access.
- Official CUT logo, colours, and typefaces have not been supplied. The interface uses neutral styling and local system fonts.
- Generic teams, dated technician memberships, and temporary zone deployments are record-only administration. They do not affect ticket access, routing, assignment, escalation, or scheduling. Regional ticket scoping, attachments, SLA timers, automatic routing, notifications/email, assets, knowledge, production integrations, and deployment remain out of scope.

## Quick start

See [Setup](docs/SETUP.md) for supported prerequisites and a safe complete sequence. In brief, from an isolated local checkout with PHP extensions and Node.js installed:

```sh
# In a fresh local shell only; prevent inherited production settings overriding .env.
unset APP_ENV APP_KEY DB_CONNECTION DB_DATABASE DB_URL
composer install --no-interaction
cp .env.example .env                 # only when .env does not already exist
php artisan key:generate
touch database/database.sqlite
npm ci --ignore-scripts
php artisan migrate --seed
npm run build
php artisan app:create-administrator # prompts for a one-time administrator password
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/login`. Keep `.env`, local databases, credentials, and real account data out of Git. `db:seed` creates role/permission definitions only; it does not create users, teams, memberships, deployments, or ticket categories/priorities. The bootstrap command is restricted to `local`/`testing` unless an operator explicitly overrides that guard. Do not use the override in production without a separately approved procedure.

## Quality checks

```sh
composer test
./vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --audit-level=moderate
```

The most recent full run reported **45 tests passed (295 assertions)**. Pint passed for 101 PHP files, Composer validation and the Vite production build passed. A fresh, disposable SQLite database ran all 15 migrations successfully and was removed after the status check. The locked Composer audit found no security advisories and npm audit found zero vulnerabilities. Tests force a test-only environment and SQLite `:memory:` database before Laravel boots, then fail closed if isolation is not active. These checks do not establish production readiness, MySQL compatibility, CUT policy approval, or a completed security review.

## Documentation

- [Phase 0 brief](docs/PHASE0_BRIEF.md)
- [Phase 1 implementation brief](docs/PHASE1_BRIEF.md)
- [Architecture and security boundaries](docs/ARCHITECTURE.md)
- [Implemented and deferred domain model](docs/DOMAIN_MODEL.md)
- [Roles and permissions](docs/ROLES_AND_PERMISSIONS.md)
- [Feature roadmap](docs/FEATURE_ROADMAP.md)
- [Requirements and open questions](docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md)
- [Development guidelines](docs/DEVELOPMENT_GUIDELINES.md)
- [Setup and administrator bootstrap](docs/SETUP.md)
- [Testing strategy and observed evidence](docs/TESTING_STRATEGY.md)
- [Laravel/PHP implementation sources](docs/PHASE1_IMPLEMENTATION_SOURCES.md)

## Next step

Request CUT ICT review of role grants, privacy/retention, category and priority values, regional authorization, branding/accessibility, database/hosting, and security requirements. Rehearse installation and migrations on the institution's chosen database before operational use. Do not infer CUT approval or production readiness from this local implementation; see the [roadmap](docs/FEATURE_ROADMAP.md).
