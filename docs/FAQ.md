# FAQ

## Contributors

**Is this production-ready?** No. It is a locally verified prototype for CUT review. See [Project scope](PROJECT_SCOPE.md).

**Which database should I use?** SQLite for local development and tests. No production engine has been approved or rehearsed — see [Deployment](DEPLOYMENT.md).

**Why are catalogues and teams empty?** The application does not invent CUT departments, locations, categories or teams. A seeded System Administrator configures them; see [Setup](SETUP.md).

**Can I create roles in the UI?** No. The role/permission catalogue is seeded code/config, with assignment/revocation available in administration. Ticket categories and priorities are separate and configurable.

**How do I test officer/technician workflows?** Seed synthetic users via `TicketDemoSeeder` (local/testing only) and assign the proposed roles through account administration. Use synthetic data only.

**Why do my tests fail against a shared database?** Inherited environment variables. Clear them as shown in [Setup](SETUP.md); the harness itself fails closed on non-testing environments.

**Where is the route/permission map?** [Route reference](ROUTE_REFERENCE.md).

## CUT ICT decisions (open questions)

The following are drawn from [Requirements and open questions](REQUIREMENTS_AND_OPEN_QUESTIONS.md) and remain unanswered. They are **not** implemented policy.

1. Product owner, approver and release sign-off.
2. Supported PHP/Laravel/database/browser versions and hosting.
3. SSO/identity source, provisioning and recovery.
4. Account creation and emergency administrator recovery.
5. Correct roles, labels, grants and whether they are region-scoped.
6. Whether the global Helpdesk Officer queue is acceptable.
7. Approved campuses/zones/buildings/rooms and off-campus representation.
8. Authoritative team, membership and deployment semantics.
9. Walk-in requester identification and minimum personal data.
10. Category/priority values, owners and rules.
11. Ticket states and resolve/close/reopen rules.
12. Internal-note readership and retention.
13. SLA calendars, escalation and notification channels.
14. Attachments, malware scanning, retention and export.
15. Privacy classification, lawful basis, retention and audit immutability.
16. Production database, credentials, backups, recovery objectives and monitoring.
17. Whether asset management is in scope or another system is authoritative.
18. Brand, colours, typography, accessibility standard and device matrix.
19. CI provider, dependency/secret scanning, review and deployment process.
20. Report audiences, metrics, timezone, suppression, export and retention.

Record each answer with approver, date and evidence before changing behaviour.

Related: [Troubleshooting](TROUBLESHOOTING.md) · [Glossary](GLOSSARY.md) · [Feature roadmap](FEATURE_ROADMAP.md)
