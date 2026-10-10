# CUT ICT Helpdesk

A digital support-management foundation for Chinhoyi University of Technology (CUT), Zimbabwe, intended to complement the existing walk-in ICT helpdesk. **Phase 1 is implemented and locally verified** for authentication, account/role administration, administrative audit, and configurable campus locations. This is not a ticketing system and is **not approved or ready for production deployment**.

## Current status

- Laravel **13.35.0** with PHP **8.3.6** in the implementation sandbox; Laravel Fortify provides the authentication backend.
- Blade, Tailwind CSS 4, and Vite provide the server-rendered administrative interface.
- SQLite is used for local development and isolated in-memory tests. MySQL/MariaDB and CUT hosting have not been verified.
- Login and logout are implemented; public registration and password reset are disabled. No SSO/LDAP/AD integration is assumed.
- Seven proposed roles and granular Phase 1 permissions are seeded. The catalogue remains subject to CUT ICT approval. No default or seeded privileged account is created.
- Users can be created, searched, viewed, edited, activated/deactivated, and assigned/revoked roles by authorized administrators. Campus, operational-zone, building, and room records have guarded administration.
- Official CUT logo, colours, and typefaces have not been supplied. The interface uses neutral styling and local system fonts; it does not fetch an unapproved third-party font.
- Tickets, teams, technician deployments, regional user assignments, assets, knowledge, notifications, SSO, email delivery, production integrations, and deployment are out of scope.

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

Open `http://127.0.0.1:8000/login`. Keep `.env`, local databases, credentials, and real account data out of Git. `db:seed` creates only the proposed role/permission catalogue; it does not create users. The bootstrap command is restricted to `local`/`testing` unless an operator explicitly overrides that guard. Do not use the override in production without a separately approved procedure.

## Quality checks

```sh
php artisan test
./vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --audit-level=moderate
```

The test base class forces a test-only application environment and SQLite `:memory:` database before the Laravel app boots, then fails closed if either is not active. The final local run reported **27 tests passed (144 assertions)**. PHP and frontend dependency audits returned no advisories at the time they were run. These checks do not establish production readiness, MySQL compatibility, institutional policy approval, or a completed security review.

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

Request CUT ICT review of proposed roles, account lifecycle, location ownership, data retention, branding/accessibility, hosting, and security requirements. Rehearse installation and migrations on the institution's chosen database before proceeding. Continue to ticket intake only after the Phase 1 foundation and open decisions have been reviewed; see the [roadmap](docs/FEATURE_ROADMAP.md).
