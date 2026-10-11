# Contributing

Thank you for helping improve CUT ICT Helpdesk.

## Before opening a change

1. Read [Setup](docs/SETUP.md), [Architecture](docs/ARCHITECTURE.md), and [Requirements and open questions](docs/REQUIREMENTS_AND_OPEN_QUESTIONS.md).
2. Keep provisional behavior clearly labeled; do not invent CUT policy, departments, locations, roles, categories, or production integrations.
3. Never commit `.env`, database files, credentials, real student/staff records, or screenshots containing sensitive information.
4. For UI changes, review the authenticated preview at desktop and narrow widths and update screenshot evidence when the change materially changes a portal.

## Development flow

```sh
composer install
npm ci --ignore-scripts
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
```

Use a feature branch with a focused name, for example `feature/location-import` or `fix/ticket-filter-label`.

## Required checks

```sh
composer test
./vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --audit-level=moderate
```

## Pull requests

A pull request should explain the problem, the behavior changed, authorization impact, migrations, tests, and any unresolved CUT decision. Include screenshots for visible changes and call out whether the screenshots use synthetic data.

Keep commits small enough to review. Do not squash away useful test or migration context when it helps reviewers understand a security or data-integrity change.
