# CI/CD

**Status:** No GitHub Actions workflows are currently configured. The repository relies on locally executed checks documented in [CONTRIBUTING.md](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/CONTRIBUTING.md) and the [PR template](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/.github/PULL_REQUEST_TEMPLATE.md). This page defines the intended pipeline.

## Required quality gates

Run locally and in CI, in this order:

```sh
composer test                         # PHPUnit feature + unit suite
./vendor/bin/pint --test               # PHP formatting/lint
npm run build                          # Vite production assets
composer audit --locked                # Composer advisories
npm audit --audit-level=moderate       # npm advisories
git diff --check                       # whitespace errors
```

A failing `composer audit --locked` must not be silently ignored. Record any accepted advisory with owner, expiry and rationale in [Dependency policy](DEPENDENCY_POLICY.md).

## Intended GitHub Actions pipeline

A `ci.yml` workflow should run on pull requests and pushes to `main`:

1. **Setup** — PHP 8.3 with locked extensions, Composer 2, Node 24; cache Composer and npm artifacts.
2. **Install** — `composer install`, `npm ci --ignore-scripts`.
3. **Checks** — run the gates above; each as a separate step so failures are attributable.
4. **Migration smoke test** — run all migrations on a fresh disposable SQLite database, then delete it.
5. **Build** — upload `public/build` as an artifact for review/deploy.

A separate `docs-pages.yml` workflow builds the MkDocs site and deploys it via the official Pages actions. See [Documentation site](#documentation-site).

## Branch and release flow

Follow [CONTRIBUTING.md](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/CONTRIBUTING.md): focused feature/fix branch → PR with the required checks green → review → merge to `main`. Findings from the repository audit are tracked in milestones and the *Audit Remediation* project board. A PR that closes an issue must reference it (`Closes #N`).

Branch protection for `main` is not yet configured. Recommended: require the PR checks above, require one approval, dismiss stale approvals, and prevent force pushes.

## Documentation site

The docs in `docs/` are built with MkDocs (`mkdocs.yml`) and published to GitHub Pages from the `docs-pages.yml` workflow using `actions/upload-pages-artifact` and `actions/deploy-pages`. Pages must be configured with source **GitHub Actions**. The `docs/` directory is canonical; the Wiki is a mirror (see [index](index.md)).

## Environment and secrets

No deployment credentials exist in this repository. Never add secrets to code, `.env` (tracked example only), logs, screenshots or audit events. If CI needs credentials, use GitHub Environments with required reviewers and least-privilege tokens.

Related: [Deployment](DEPLOYMENT.md) · [Dependency policy](DEPENDENCY_POLICY.md) · [Testing strategy](TESTING_STRATEGY.md)
