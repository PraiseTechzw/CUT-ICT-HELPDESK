# Operations Runbook

**Status:** Draft. No production environment exists yet. These procedures must be rehearsed on a disposable copy and owned by a named CUT ICT operator before use.

## Daily / routine

- Review application logs (`storage/logs/laravel.log` with the configured `LOG_CHANNEL`) for `ERROR` and security-audit events.
- Check the admin audit screen (`admin.audit.index`) for unexpected role, permission, location or status changes.
- Confirm scheduled work (if configured) and queue health; the example queue connection is `database`, backed by the `jobs` table.
- Verify free disk space for logs, SQLite/database storage and uploaded assets.

## Backup

Back up **both** the database and the application file storage. There is no user-file upload pipeline yet, but the `ticket_attachments` table exists in schema.

- Database: use engine-native logical backup (e.g. `sqlite3 database.sqlite .dump` locally, or `mysqldump` for a CUT-selected engine). Store encrypted, off-host.
- Files: `storage/` (logs, framework cache, any future uploads) and `.env` (secret store, not Git).
- Schedule: agree RPO with CUT ICT. Until then, back up before every deploy and at a regular daily interval.

## Restore (rehearse first)

1. Announce the restore and freeze changes.
2. Stop incoming traffic or enable maintenance mode: `php artisan down`.
3. Restore the database backup into a verified disposable target first; check row counts and a known ticket.
4. Restore file storage and `.env` from the secret store.
5. Run `php artisan optimize:clear && php artisan optimize`.
6. Smoke-test login, ticket creation, triage, reports and notifications.
7. Bring the app up: `php artisan up`. Record the restore, operator, duration and outcome.

## Common tasks

| Task | Command / location |
| --- | --- |
| Create administrator (local/testing only) | `php artisan app:create-administrator` |
| Seed synthetic ticket demo data (local/testing only) | `php artisan db:seed --class=TicketDemoSeeder` |
| Clear all caches | `php artisan optimize:clear` |
| Rebuild caches | `php artisan optimize` |
| List routes | `php artisan route:list` |
| Inspect failed jobs | `php artisan queue:failed` (when a failed-jobs table exists) |
| Tail logs | `php artisan pail` (local) or central log viewer |

## Monitoring

No monitoring or alerting is configured. Minimum before production: host/disk/CPU alerts, HTTP availability check on `/login`, error-rate alerting on logs, and alerting on unexpected audit-event volume. See [Deployment](DEPLOYMENT.md) for health-check notes.

## Incident handling

Follow [Security incident response](SECURITY_INCIDENT_RESPONSE.md) for suspected security events. Preserve logs and audit events; do not delete or rewrite them during containment. Record all actions with timestamps.

## Related

[Deployment](DEPLOYMENT.md) · [Database schema](DATABASE_SCHEMA.md) · [Troubleshooting](TROUBLESHOOTING.md) · [Dependency policy](DEPENDENCY_POLICY.md)
