# Troubleshooting

Symptom → likely cause → fix. Environment and database safety first: see [Setup](SETUP.md).

| Symptom | Likely cause | Fix |
| --- | --- | --- |
| Tests target a shared/production database | Inherited `APP_ENV`, `DB_*` or `DB_URL` in the shell | `unset APP_ENV APP_KEY DB_CONNECTION DB_DATABASE DB_URL`; rerun. The test harness forces `APP_ENV=testing` before boot — never weaken that guard |
| `SQLSTATE: unable to open database file` on local setup | Missing SQLite file | `touch database/database.sqlite`, then `php artisan migrate` |
| Blank/unstyled pages under `php artisan serve` | Vite build missing | `npm ci --ignore-scripts && npm run build`. The Blade layout expects `public/build/manifest.json` |
| Build warning about `/banner-image.png` | Absolute public-path asset reference in CSS | Known finding; the asset is served from `public/`. Fix tracked in the audit remediation project (bundle the image via `resources/images/`) |
| `No application encryption key has been specified` | `.env` missing or `APP_KEY` empty | `cp .env.example .env` then `php artisan key:generate` |
| Login always fails for a newly created account | Account is inactive or has no role | Check `is_active` and role assignment in account administration |
| `403` on an admin/ticket page | Missing permission for the routed gate | Check [Roles and permissions](ROLES_AND_PERMISSIONS.md) and the route's `can:` middleware; roles are seeded, not self-service |
| Ticket categories/priorities empty | Catalogues are intentionally unseeded | System Administrator configures them at `admin/ticket-catalogues/*` |
| Notifications not appearing | Database channel only; recipient rules apply | Check the inbox at `/notifications`; no email/SMS is sent |
| Stale config/routes/views after changes | Cached artifacts | `php artisan optimize:clear` (then `php artisan optimize` in production) |
| Pint reports style failures | Unformatted PHP | `./vendor/bin/pint` (review the diff before committing) |
| `composer audit` fails with CVEs | Locked transitive Symfony/Laravel versions | Plan a reviewed `composer update` per [Dependency policy](DEPENDENCY_POLICY.md) |
| Dark mode shows white cards with pale text | Views missing `dark:` variants | Known finding tracked in the UI/UX milestone; temporarily switch to light appearance |
| Need synthetic tickets to review queues | No demo data seeded locally | `php artisan db:seed --class=TicketDemoSeeder` (local/testing only; credentials in [Setup](SETUP.md)) |

## Getting help

Check [FAQ](FAQ.md), the [documentation index](index.md) and the [support guide](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/.github/SUPPORT.md). For security issues follow [SECURITY.md](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/SECURITY.md) and [Security incident response](SECURITY_INCIDENT_RESPONSE.md). Do not paste `.env`, credentials or personal data into issues.
