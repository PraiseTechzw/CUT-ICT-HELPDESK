# CUT ICT Helpdesk

A planned digital helpdesk and support-management system for Chinhoyi University of Technology (CUT), Zimbabwe. The intended service complements the existing walk-in ICT helpdesk and centralizes authorized support-request handling. This repository is at **Phase 0: architecture, requirements, and engineering foundation**; it is not yet a runnable Laravel application.

## Current repository status

The initial repository contained only `.gitignore`, `LICENSE`, and this placeholder README. There was no Laravel source, Composer manifest or lockfile, frontend manifest, migrations, test suite, environment example, or project documentation. The working tree was clean at inspection. In the current development environment, Git, Node.js 22, npm 10, and a MySQL client are available; PHP and Composer are not. No application runtime, database connection, tests, or integrations have been verified.

## Intended technology direction

- Backend: Laravel / PHP, using a modular monolith and Laravel conventions.
- Database: MySQL or MariaDB unless verified institutional infrastructure dictates otherwise.
- Frontend: evaluate Laravel Blade and reusable components before introducing a separate frontend.
- Tests: Laravel-supported PHPUnit or Pest, selected after project initialization.
- Authentication: Laravel-compatible; university identity integration requires authorization and technical verification.

Exact supported versions must be selected during bootstrap based on Laravel's current requirements and the institution's hosting/runtime constraints. No framework or dependency has been installed by Phase 0.

## Documentation

- [Phase 0 source brief](docs/PHASE0_BRIEF.md)
- [Project scope](docs/PROJECT_SCOPE.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Domain model](docs/DOMAIN_MODEL.md)
- [Roles and permissions](docs/ROLES_AND_PERMISSIONS.md)
- [Feature roadmap](docs/FEATURE_ROADMAP.md)
- [Requirements and open questions](docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md)
- [Development guidelines](docs/DEVELOPMENT_GUIDELINES.md)
- [Setup](docs/SETUP.md)
- [Testing strategy](docs/TESTING_STRATEGY.md)

## Prerequisites and setup

The application has not yet been initialized. Before bootstrapping, install a PHP version supported by the Laravel release selected for the project, Composer, required PHP extensions, Node.js and the chosen package manager, and a supported MySQL/MariaDB service. Follow [Setup](docs/SETUP.md) for the safe initialization sequence; do not run those commands until versions and institutional hosting constraints are confirmed.

## Development commands

No application commands are available yet because `artisan`, `composer.json`, and `package.json` do not exist. Once the app is initialized, the expected Laravel workflow will generally include:

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test
```

These are future commands, not commands verified in this repository. Use only after the bootstrap and environment configuration steps in `docs/SETUP.md`.

## Project status and next step

Phase 0 proposes architecture and a phased plan; it does not implement ticketing, migrations, authentication, integrations, or deployment. The next phase is **Phase 1: confirm bootstrap constraints, initialize a compatible Laravel application, and define authentication/authorization foundations**. See [Feature roadmap](docs/FEATURE_ROADMAP.md) and [Requirements and open questions](docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md) for acceptance criteria and dependencies.
