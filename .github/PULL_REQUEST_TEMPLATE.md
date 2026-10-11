## Summary

Describe the user-visible or technical change.

## Scope and policy

- [ ] I identified any CUT policy or product decision this change assumes.
- [ ] I did not invent institutional records, roles, routes, or integrations.
- [ ] Authorization remains enforced server-side.
- [ ] Secrets and personal data are absent from code, logs, screenshots, and fixtures.

## Verification

- [ ] `composer test`
- [ ] `./vendor/bin/pint --test`
- [ ] `npm run build`
- [ ] `composer audit --locked`
- [ ] `npm audit --audit-level=moderate`

## UI changes

- [ ] Reviewed desktop and narrow-width layouts.
- [ ] Reviewed keyboard labels, validation/empty states, and interactive disclosures.
- [ ] Added or updated synthetic-data screenshots when useful.

## Notes for reviewers

Include migrations, rollout considerations, known limitations, and links to screenshots or documentation.
