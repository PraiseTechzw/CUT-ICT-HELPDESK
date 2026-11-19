# Security Incident Response (Draft)

**Status:** Draft plan. No production incident process or named owner exists yet. CUT ICT must appoint owners and approve this plan before operational use. See [SECURITY.md](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/SECURITY.md) for how to *report* a vulnerability.

## Roles to appoint

| Role | Responsibility |
| --- | --- |
| Incident Commander | Coordinates response, owns decisions and timeline |
| Technical Lead | Investigates, contains and eradicates |
| Communications Lead | Notifies affected parties and CUT stakeholders |
| Evidence Custodian | Preserves logs, audit events and snapshots |

## Severity model

| Severity | Example |
| --- | --- |
| Critical | Active unauthorised access to ticket data or privileged account compromise |
| High | Exploitable access-control flaw; credential exposure |
| Medium | Limited data exposure; suspicious authenticated activity |
| Low | Policy deviation with no demonstrated impact |

## Response steps

1. **Intake** — record source, time, affected system and description. Assign an Incident Commander.
2. **Triage** — assess severity and scope using logs and `audit_events`. Do not delete or rewrite evidence.
3. **Containment** — revoke affected sessions/credentials, disable compromised accounts (`is_active=false`), apply maintenance mode if needed, block offending network sources.
4. **Eradication** — remove the cause (patch, configuration fix, credential rotation). Follow [Dependency policy](DEPENDENCY_POLICY.md) for vulnerable packages.
5. **Recovery** — restore from a verified backup if data was altered (see [Operations runbook](OPERATIONS_RUNBOOK.md)); re-run smoke tests; monitor for recurrence.
6. **Notification** — notify CUT ICT and affected individuals per the approved policy and legal basis (currently undefined — Q15 in [Requirements](REQUIREMENTS_AND_OPEN_QUESTIONS.md)).
7. **Post-incident review** — document root cause, timeline, impact, actions and prevention tasks within five working days; file follow-up issues in the Audit Remediation project.

## Evidence to preserve

Application and web-server logs, `audit_events`, queue/job records, database snapshots, relevant configuration and the exact deployed commit. Store encrypted with restricted access and a defined retention period.

## Current limitations

No alerting, no on-call rota, no approved notification thresholds, no tested restore and no immutability guarantee for audit events (they are guarded at the application level only). These are Phase 9 items.

Related: [Deployment](DEPLOYMENT.md) · [Operations runbook](OPERATIONS_RUNBOOK.md) · [Dependency policy](DEPENDENCY_POLICY.md)
