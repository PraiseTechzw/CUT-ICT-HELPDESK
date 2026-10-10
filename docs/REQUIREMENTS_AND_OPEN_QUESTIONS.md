# Requirements and Open Questions

**Purpose:** Separate provided requirements from design proposals and facts actually verified in this repository. No CUT ICT operational process has been independently verified.

## Verified repository facts

- Repository: `PraiseTechzw/CUT-ICT-HELPDESK`, cloned from its `main` branch at initial commit `470a680` (`Initial commit`).
- Initial tracked files: `.gitignore`, `LICENSE`, and an 18-byte README containing only `# CUT-ICT-HELPDESK`.
- Initial working tree was clean; there were no Laravel manifests/source, frontend package manifest, migrations, tests, environment example, or docs.
- In this sandbox: Git, Node.js v22.13.0, npm 10.9.2, and a MySQL client are present; `php` and `composer` are absent. No database service/connection was verified.
- The full user-supplied Phase 0 brief is preserved verbatim in `docs/PHASE0_BRIEF.md`.

## User-provided requirements (not external verification)

CUT is in Chinhoyi, Zimbabwe; the digital service is to complement a physical walk-in ICT helpdesk. User groups include students, employees, authorized requesters, walk-in staff, technicians across campus zones, supervisors, specialist ICT teams, central management, administrators, and auditors. Regional configuration must remain flexible; actual zones/boundaries, staffing, deployments, and escalation procedures are not confirmed. Potential categories include computers, hardware, software, printers, accounts, connectivity, Wi-Fi, labs, and other ICT services. Desired eventual functions include intake, assignment, tracking, communication, escalation, resolution, feedback, reports, knowledge, and assets. Preferred direction: Laravel/PHP, SQL (prefer MySQL/MariaDB absent contrary infrastructure evidence), existing frontend if present, otherwise evaluate Blade/light JavaScript, Laravel-compatible auth, and Git/Composer/tool-appropriate dependency management.

## Proposed decisions for review

Modular monolith using Laravel conventions; one SQL database; Blade-first evaluation; effective-dated organizational relationships and immutable history; server-side authorization and deny-by-default; separate internal notes from requester-visible messages; no guessed branding, zones, service catalogue, escalation rule, or SSO; no implementation until runtime and institutional requirements are understood. Rationale appears in `ARCHITECTURE.md`.

## Unverified assumptions and risks

The exact Laravel release, PHP extensions, hosting target, database engine/version, authentication source, university identity provider, email/SMS services, frontend approach, accessibility standards, official CUT branding, data classification, retention/legal basis, backup/recovery objectives, and integrations remain unknown. Risks include over-collecting personal data, cross-zone exposure, ambiguous walk-in identity, unreviewed retention, misrouted tickets, and conflicting asset records. These are design risks, not evidence of a current incident.

## Questions for CUT ICT before implementation

1. Who is the product owner and who approves requirements, access policy, and releases?
2. What PHP/Laravel/database versions and hosting environments are supported or mandated?
3. Is university SSO required? Which identity source, identifiers, test tenant, and account lifecycle apply?
4. What are the approved campuses, zones, buildings/rooms, and responsible configuration owners? How are unknown/off-campus locations triaged?
5. What are the actual ICT teams, staffing assignments, multi-zone responsibilities, and effective-date/overlap rules for temporary deployments?
6. How are walk-in requesters identified when they lack or decline a university account? What minimal data may be recorded?
7. Which request categories, priority definitions, statuses, service hours, SLA calendars, pause conditions, and escalation pathways are approved?
8. Which users may assign/transfer tickets across zones, and what audit trail is required?
9. Which communications are public vs internal? Which notification channels/providers are approved and who owns delivery failures?
10. What attachment types/sizes are needed; where may files reside; what malware scanning, access, retention, and deletion rules apply?
11. What personal data classification, privacy notice, lawful basis, retention schedule, export/deletion process, and audit access are required?
12. What backup frequency, recovery objectives, monitoring, incident-response owner, and hosting controls are mandatory?
13. Is an asset registry in scope, or is another institutional system the authoritative source?
14. Which official logo, palette, typography, slogan, design guidelines, and accessibility target are approved?
15. What volumes, peak load, browser/mobile/device support, languages, and operational acceptance criteria must be met?
16. What approved test data, staging environment, code review, dependency scanning, and CI/release process should be used?

Record answers, approver, date, and evidence in project documentation before converting them into production behavior.
