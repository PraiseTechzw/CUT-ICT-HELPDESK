# Dependency and Vulnerability Policy

**Status:** Draft. Establishes how dependencies are updated and how advisories are handled. CUT ICT must approve severity gates and waiver authority.

## Current state (observed 2026-11-19)

On 2026-11-19 the audit found **11 advisories affecting 7 packages** (Symfony serializer/http-foundation/routing/http-kernel/mime and Laravel framework). A reviewed `composer update --with-all-dependencies` moved the lockfile to patched versions (Symfony 7.4.23, Laravel 13.43.0), and `composer audit --locked` now reports **no advisories**. `npm audit --audit-level=moderate` reports **0 vulnerabilities**. The advisory state is re-checked by CI on every pull request; if new advisories appear, this section must be updated with the date, affected packages and disposition.

## Update cadence

| Class | Cadence |
| --- | --- |
| Security patches | As soon as a fix is available; treat Critical/High as urgent |
| Minor/patch updates | Monthly, batched and reviewed |
| Major upgrades | Planned work with migration rehearsal and CUT approval |

Use the locked versions for installs. Do not run unconstrained `composer update` in production; review the diff and run all quality gates afterward.

## Severity gates

Run `composer audit --locked` and `npm audit --audit-level=moderate` on every PR (see [CI/CD](CI_CD.md)).

- **Critical / High:** block release; patch before merge unless a time-bound waiver is approved.
- **Medium:** patch in the current cycle; document if deferred.
- **Low:** schedule; record rationale.

Reachability matters: a transitive dependency with no reachable vulnerable code path may be waived, but the waiver must name the code path, owner and expiry date. Dev dependencies are not automatically exempt — CI executes with elevated credentials.

## Waiver process

A waiver requires: advisory ID, affected version range, demonstrated reachability (or lack of it), compensating controls, named approver, expiry date and a linked issue to remove the waiver. Waivers are recorded in this document and reviewed monthly. CUT ICT owns approval for production.

## License review

Keep dependencies under permissive licenses compatible with the project's MIT license. Record any new license type at adoption; do not add copyleft dependencies without review.

## Locks and supply chain

Commit `composer.lock` and `package-lock.json`. Use `composer install --no-interaction` and `npm ci --ignore-scripts`. Avoid lifecycle scripts from untrusted packages. Review new maintainers and package ownership changes. See [Security incident response](SECURITY_INCIDENT_RESPONSE.md) for exploitation response.

Related: [CI/CD](CI_CD.md) · [Testing strategy](TESTING_STRATEGY.md) · [Deployment](DEPLOYMENT.md)
