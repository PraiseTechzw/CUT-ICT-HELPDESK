# Roles and Permissions

**Status:** Proposal for CUT ICT review. These are conceptual roles, not confirmed institutional job titles or an implemented access-control configuration.

## Proposed roles

- **Requester**: submits and follows their own requests; may be a student, employee, or other authorized person.
- **Walk-in helpdesk registrar**: creates a ticket on behalf of a walk-in requester and views only the records needed for intake and follow-up.
- **Technician**: views and updates tickets assigned to them or otherwise explicitly in their authorized scope; records work and communicates with requesters.
- **Zone supervisor**: manages queues and assignments for approved zones, subject to explicit cross-zone exceptions.
- **Specialist team member/lead**: accesses tickets routed to their team and performs approved team-level assignment actions.
- **Central ICT manager**: cross-zone operational oversight and configuration/reporting access as authorized.
- **System administrator**: manages technical configuration and accounts; administrative access does not automatically grant permission to read all ticket content.
- **Auditor**: read-only access to approved audit/operational records, with sensitive content limited by mandate.

Roles may be combined only where approved. A person can have multiple scoped assignments; authorization must be evaluated server-side on each request.

## Illustrative permission matrix

| Capability | Requester | Registrar | Technician | Zone supervisor | Specialist team | Central manager | System admin | Auditor |
|---|---|---|---|---|---|---|---|---|
| Create own ticket | Yes | On behalf, if authorized | If policy permits | If policy permits | If policy permits | If policy permits | Not by admin status alone | No |
| View requester-visible ticket | Own | Intake scope | Assigned/authorized scope | Authorized zone(s) | Authorized team scope | Authorized cross-zone scope | Not by admin status alone | Mandate-limited |
| Add requester-visible message | Own ticket | If delegated workflow permits | Assigned scope | If permitted | If permitted | If permitted | No by admin status alone | No |
| Add internal note/work log | No | Limited intake note, if approved | Assigned scope | Authorized zone(s) | Authorized team scope | Authorized scope | No by admin status alone | Read-only if mandated |
| Assign/transfer | No | Intake queue only if approved | Limited/none unless delegated | Approved zone(s) | Team queue only | Authorized cross-zone | Configuration only; no operational privilege implied | No |
| Manage zones/teams/catalogues | No | No | No | Limited delegated scope if approved | No | Approved system configuration only | Yes, but separate from ticket-content permission | No |
| View reports/audit | Own status only | Limited | Assigned scope | Authorized zone(s) | Team scope | Authorized management scope | Technical admin audit as needed | Approved read-only scope |

This table is a starting point only. Exact role/permission combinations and separation-of-duties rules require institutional approval.

## Regional access rules

1. Derive zone/team authorization from current effective-dated assignments and the requested record—not from client-supplied zone IDs.
2. Deny by default; tests must cover direct URL/API access to another zone's ticket, history, notes, attachments, reports, and export endpoints.
3. Cross-zone work is an explicit grant or documented delegation, time-bounded where appropriate and auditable.
4. Preserve historical actors and assignments even when a technician leaves a zone or a zone is reorganized. Historical record visibility still follows the approved audit policy.
5. Unknown/unassigned location does not grant broad access; route it to a designated triage workflow once that workflow is approved.
6. System administration is distinct from permission to read sensitive ticket content. Use separate capabilities for identity/configuration administration and operational data access.
7. Internal notes and private attachments must never appear in requester-facing responses, notifications, exports, or caches.

## Responsibilities requiring confirmation

CUT ICT must identify the role/permission approver, account lifecycle owner, zone and team administrators, cross-zone delegation authority, audit access owner, escalation decision maker, data protection/security contacts, and whether student/employee identity sources differ. No SSO, production administrator, or real staff account is assumed or created by Phase 0.
