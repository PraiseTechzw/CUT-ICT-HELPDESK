# Setup and Bootstrap

**Current state:** Documentation-only repository; no Laravel application is installed. `php` and `composer` were not found in the Phase 0 sandbox. No database connection was tested. Do not run application setup commands until the environment and versions are approved.

## Prerequisites to confirm and install

Obtain the institutional hosting/deployment requirements, then select a supported Laravel release and its compatible PHP version/extensions from the official Laravel requirements. Install that PHP runtime and Composer, Node.js and the selected package manager, and a supported MySQL/MariaDB server/client or the institution-mandated SQL equivalent. Decide whether local development uses Docker or host services; no container tooling was verified here. Record actual version outputs in onboarding docs when established.

## Safe bootstrap sequence for this existing Git repository

The repository is not literally empty: it contains Git history, `.gitignore`, `LICENSE`, `README.md`, and Phase 0 docs. **Do not run `composer create-project laravel/laravel .` in this directory**; Composer may reject a non-empty target or overwrite/conflict with existing project files.

After approving versions and reviewing a clean working tree:

1. After recording the institution-approved Laravel release in `APPROVED_LARAVEL_VERSION`, create Laravel in a temporary directory. The shell guard intentionally stops if the version has not been approved/set:

   ```sh
   export LARAVEL_VERSION="${APPROVED_LARAVEL_VERSION:?Set APPROVED_LARAVEL_VERSION to the approved release first}"
   composer create-project laravel/laravel /tmp/cut-ict-helpdesk-app "$LARAVEL_VERSION"
   ```

2. Review the generated files and PHP/platform requirements. Decide whether Laravel's default frontend starter kit is needed; do not introduce one without an approved authentication/UI decision.
3. Merge the generated application into the repository, preserving `.git`, the existing `LICENSE`, README, and `docs/`. Review every collision rather than overwriting blindly. Example only after review, using `rsync` if installed:

   ```sh
   REPO_DIR=/home/ubuntu/CUT-ICT-HELPDESK
   rsync -a --exclude='.git/' --exclude='LICENSE' --exclude='README.md' --exclude='docs/' /tmp/cut-ict-helpdesk-app/ "$REPO_DIR/"
   ```

   The generated `.gitignore` may differ; compare and merge its Laravel rules instead of allowing an unreviewed replacement. Do not copy `.env` or generated secrets from another environment.
4. From the repository root, inspect `composer.json`, `.env.example`, generated frontend manifests, and git diff. Configure required dependencies and local environment privately. Never commit `.env` or credentials.
5. Only when `.env` is absent, create it from the reviewed example; generate an application key locally:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

6. Configure a dedicated, non-production development database with least-privilege credentials, then verify:

   ```sh
   php artisan about
   php artisan migrate:status
   php artisan test
   ```

   Run migrations only after the first schema is reviewed; do not run against production. Follow `docs/TESTING_STRATEGY.md`.
7. Inspect `git status` and the complete diff. Commit only reviewed source/docs; exclude `.env`, credentials, generated secrets, caches, and local data.

## Usual commands after initialization

These are expected Laravel commands only after initialization; they are not available or verified today:

```sh
composer install
npm install                 # use the package manager actually selected by the generated app
cp .env.example .env        # only if no local .env exists
php artisan key:generate
php artisan test
npm run build               # only if defined by package.json
```

For local serving, use the command documented by the selected Laravel version and frontend setup. Do not guess or record a command as successful until it has actually run.

## Database configuration and migrations

Select the SQL engine/version with institutional support. Create an isolated development database; set connection name, host, port, database, username and password only in local ignored environment configuration. Verify connectivity through Laravel after bootstrap without printing secrets. Do not invent zones or staff as seed data. Migrations and reference-data seeders should follow a reviewed domain model and use synthetic test fixtures.

## Troubleshooting and safeguards

- `php`/`composer` missing: install approved versions; current Phase 0 sandbox lacks them.
- Composer platform conflict: verify Laravel/PHP/extension compatibility; do not bypass platform checks or blindly upgrade.
- Existing-file conflicts during bootstrap: stop and review the generated diff; preserve Git history, license, Phase 0 docs, and approved README.
- Database connection error: verify local service state and non-secret connection settings; never paste credentials into tickets or logs.
- Migration uncertainty: use a disposable database and inspect SQL/diff; never test destructive changes on institutional data.
- Identity, email, SSO, and other integrations remain disabled until authorized and technically verified.
