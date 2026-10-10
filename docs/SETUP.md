# Setup and Bootstrap

**Status:** A Laravel application is present. The following procedure is for an isolated local development environment only; it is not a production deployment guide.

## Verified implementation environment

This Phase 1 implementation was checked in a sandbox with PHP 8.3.6, Composer 2.7.1, Laravel 13.35.0, Node.js 22.13.0, npm 10.9.2, and Vite 8.3.4. The installed PHP extensions support SQLite and the Laravel application. The application and tests were exercised with SQLite; no CUT-hosted runtime, production database, MySQL/MariaDB service, institutional identity provider, or mail service was verified. Confirm CUT's supported versions and hosting requirements before deployment.

Laravel 13 requires PHP 8.3 or later; the official compatibility and authentication references are recorded in [Phase 1 implementation sources](PHASE1_IMPLEMENTATION_SOURCES.md). Install Composer, Node.js/npm, and the PHP extensions required by the locked dependencies, including PDO SQLite for local testing.

## Local installation

From the repository root, on a clean local checkout:

Use a fresh local shell and ensure inherited Laravel environment variables do not point to a shared/production system. In Bash or `sh`, clear the common overrides before invoking Artisan:

```sh
unset APP_ENV APP_KEY DB_CONNECTION DB_DATABASE DB_URL
```

The tracked `.env.example` then selects `APP_ENV=local` and SQLite, and `key:generate` writes a local key into the ignored `.env`. If you use another shell, apply the equivalent there. Verify locally that the active database is the intended disposable SQLite file before running migrations; never print credentials or target settings into shared logs.

1. Install the locked dependencies:

   ```sh
   composer install --no-interaction
   npm ci --ignore-scripts
   ```

2. Create local configuration only if it does not already exist, then generate a local application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   Review `.env` locally without printing or sharing its contents. It is ignored by Git. The example defaults to a local SQLite database and contains no real credentials.

3. Create the local SQLite file if it is absent, then migrate and seed the role/permission catalogue:

   ```sh
   touch database/database.sqlite
   php artisan migrate --seed
   ```

   The seeder creates proposed role and permission definitions only—no users, CUT locations, or privileged default account.

4. Build the frontend and create the first administrator interactively:

   ```sh
   npm run build
   php artisan app:create-administrator
   ```

   The command asks for a name, email, and password (minimum 12 characters plus confirmation). It hashes the password, assigns the proposed System Administrator role, and does not print or log the password. It refuses to run outside `local`/`testing` unless the explicit `--force` override is supplied. That override is **not** an approved production bootstrap procedure; do not use it in production without a separately reviewed operational plan.

5. Start the local web server:

   ```sh
   php artisan serve --host=127.0.0.1 --port=8000
   ```

   Visit `http://127.0.0.1:8000/login`. The Vite production build must exist for the Blade layouts to load assets in the default local web-server flow.

## Tests and quality checks

```sh
php artisan test
./vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --audit-level=moderate
```

The test harness sets `APP_ENV=testing`, a test-only application key, array-backed session/cache, synchronous queue, and SQLite `:memory:` **before application bootstrap**. It fails closed unless Laravel reports the `testing` environment and SQLite memory database. Thus an inherited shell variable such as `APP_ENV=PROD` cannot redirect the suite to a configured institutional database. Never weaken this guard or point tests at shared data.

## Authentication and account operation notes

- Login is served by Fortify. Login attempts are rate-limited; inactive accounts cannot authenticate and are logged out of protected routes if deactivated after login.
- Public registration, profile self-service, and password-reset/email flows are disabled. SSO, LDAP, and Active Directory are not configured.
- Create accounts through the authorized account-management screens. New accounts receive only the proposed Requester role.
- There is not yet an in-app password-reset workflow for existing accounts. Agree an account recovery process and mail/identity ownership with CUT ICT before operational use.
- Role definitions and grants are seeded. The application supports assignment/revocation, but not web-based creation/editing of the role or permission catalogue.

## Database and migration safeguards

The local `.env.example` selects SQLite for the least-setup development path. The tests use an isolated in-memory database, independently of `.env`. Production SQL engine/version, backup/restore, session/caching topology, connection credentials, and migration rollout remain unverified. Choose these with CUT ICT before deployment. Do not use `migrate:fresh`, database wiping, or local sample data on an institutional/shared database. Run reviewed forward migrations only against a purpose-built environment.

## Configuration and production boundary

The example file is not a production configuration. A real deployment needs a secret, unique application key; HTTPS; secure session/cookie settings; `APP_DEBUG=false`; least-privilege credentials; a supported SQL database; operational backups and tested restore; monitored logs; an approved administrator recovery process; and a reviewed retention/privacy/access policy. Identity, email, notifications, SSO, internal integrations, and production infrastructure changes have not been configured or authorized here. Do not put credentials in Git, chat, screenshots, build logs, or audit metadata.
