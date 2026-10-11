# Security Policy

## Scope

This repository is a development foundation for a university ICT helpdesk. It is not approved for production use until CUT ICT completes its security, privacy, hosting, identity, retention, and operational reviews.

## Reporting a vulnerability

Do not open a public issue for a suspected vulnerability. Contact the repository maintainers through the private security contact configured for the GitHub repository, or use GitHub's private vulnerability reporting feature when enabled. Include:

- affected route, component, or commit
- reproducible steps or a minimal proof of concept
- impact and required privileges
- logs or screenshots with secrets and personal data removed

Allow maintainers reasonable time to investigate before public disclosure. Never test against a production or institutional system without explicit written authorization.

## Development safeguards

- Keep secrets in ignored environment files and secret stores, never in Git.
- Use synthetic accounts and data for local screenshots and demos.
- Preserve server-side authorization; navigation visibility is not a security boundary.
- Keep CSRF protection, active-account enforcement, policy checks, audit-safe payloads, and privacy boundaries covered by tests.
- Treat the local SQLite setup as disposable development infrastructure, not a production recommendation.

## Current known boundary

SSO/LDAP/AD, email delivery, password-reset mail, attachments, malware scanning, production deployment, external integrations, and institutional retention controls are not configured. Review [Architecture](docs/ARCHITECTURE.md) before proposing changes in these areas.
