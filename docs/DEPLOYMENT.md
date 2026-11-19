# Deployment Guide

**Status:** Draft. This repository has **not** been deployed. Treat this as a checklist to be reviewed and completed with CUT ICT before any operational use. It does not authorize a release. See [Feature roadmap](FEATURE_ROADMAP.md) Phase 9 and [Requirements](REQUIREMENTS_AND_OPEN_QUESTIONS.md) Q2, Q16, Q19.

## 1. Prerequisites before deployment

CUT ICT must confirm, in writing:

- Supported PHP and web-server versions (the code targets PHP 8.3+ and was verified on 8.3.6).
- Production SQL engine and version. Only SQLite has been exercised; no MySQL/MariaDB migration rehearsal has occurred.
- Hosting model, network exposure, TLS termination and trusted-proxy topology.
- Identity source (local accounts today; SSO/LDAP/AD are not implemented).
- Mail delivery owner (not implemented; `MAIL_MAILER=log` is the example default).
- Data classification, lawful basis, retention schedule and backup objectives.
- Named product owner, release approver and incident contact.

## 2. Production configuration checklist

Set these in the environment of the target host; never commit them.

| Setting | Required value / note |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Unique, generated per environment (`php artisan key:generate`) |
| `APP_URL` | Canonical HTTPS URL |
| `DB_CONNECTION` / credentials | CUT-approved engine; least-privilege account |
| `SESSION_DRIVER` | `database` (example default) or CUT-approved shared store |
| `SESSION_ENCRYPT` | Consider `true` for sensitive sessions |
| `SESSION_SECURE_COOKIE` | `true` behind HTTPS (set in `config/session.php` if not env-driven) |
| `CACHE_STORE` / `QUEUE_CONNECTION` | `database` defaults; decide topology with CUT |
| `MAIL_*` | CUT-approved provider; current code sends no mail |
| `TRUSTED_PROXIES` | Set only when behind a known proxy; review `TrustProxies` middleware |
| `LOG_LEVEL` | `info` or `warning`; central log shipping |

Do not use the `app:create-administrator --force` override as a production bootstrap. The interactive command is a local/testing convenience. Agree a reviewed privileged-account and recovery process first.

## 3. First deployment

```sh
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci --ignore-scripts
npm run build
cp .env.example .env   # then edit values on the host; never in Git
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Point the web server document root at `public/`. Deny access to all other directories. Serve only over HTTPS.

## 4. Upgrade deployment

1. Take and verify a backup before changing code or schema (see [Operations runbook](OPERATIONS_RUNBOOK.md)).
2. Pull the approved release, install locked dependencies, rebuild assets.
3. Review every new migration before execution; run reviewed **forward** migrations only.
4. Clear and rebuild caches: `php artisan optimize:clear && php artisan optimize`.
5. Smoke-test login, ticket creation, triage, assignment, reports and notifications.
6. Record the release, approver, timestamp and rollback reference in `CHANGELOG.md`.

Never use `migrate:fresh`, `db:wipe`, demo seeders or local sample data against an institutional database.

## 5. Health checks and rollback

A minimal health check is `php artisan about --only=environment` plus an authenticated request to `/dashboard`. There is no dedicated health endpoint yet (tracked in Phase 9).

Rollback is a documented rehearsal, not an untested command: restore the pre-deploy database backup, redeploy the previous release artifact, rebuild caches, and re-run the smoke tests. Rehearse this on a disposable copy before relying on it.

## 6. Known production gaps

Global Helpdesk Officer queue; no regional ticket scoping; no attachments, SLA, routing, email, SSO or retention enforcement; audit-event guards are application-level only; no restore-tested backup, monitoring or incident process is in place. These are tracked in [Requirements](REQUIREMENTS_AND_OPEN_QUESTIONS.md) and [Security incident response](SECURITY_INCIDENT_RESPONSE.md).
