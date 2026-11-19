# Accessibility Statement

**Status:** Draft. The application has not had an automated or user-tested accessibility audit. This statement records the current position honestly; it is not a conformance claim.

## Proposed target

WCAG 2.2 Level AA is proposed as the conformance target, pending CUT ICT approval (Q18 in [Requirements](REQUIREMENTS_AND_OPEN_QUESTIONS.md)).

## What has been done

- Keyboard labels and `aria-label`s present on icon-only controls.
- Decorative SVGs marked `aria-hidden`; Lucide replacement sets `focusable="false"`.
- Viewport meta present; `prefers-reduced-motion` handled in `app.css`.
- Status messages use `role="status"` / `role="alert"`.
- Some tables use `scope="col"`.
- Manual visual review recorded in [UI review](UI_REVIEW.md) — labelled a *review*, not an audit.

## Known limitations (from the 2026-11-19 static audit)

| Area | Impact |
| --- | --- |
| Skip-to-content link missing | Keyboard/screen-reader users traverse full navigation on every page (WCAG 2.4.1) |
| Dark mode on summary/catalogue/notification views | Near-white text on white cards (WCAG 1.4.3) |
| Theme applied after paint; toggle label starts wrong | Flash of incorrect theme; inaccurate accessible name (WCAG 1.4.3 / 4.1.2) |
| Validation errors not programmatically tied to most fields | Screen readers cannot identify failed inputs (WCAG 3.3.1 / 1.3.1) |
| Live search filters have no `aria-live` region | Result changes are silent (WCAG 4.1.3) |
| Several tables missing `scope` and captions | Header association relies on heuristics (WCAG 1.3.1) |
| Some images lack intrinsic dimensions | Layout shift (not a WCAG AA failure) |
| "Forgot password?" is a non-focusable span | Link-like control is unreachable (WCAG 2.1.1) |
| `lang="en"` hardcoded on authenticated pages | Wrong pronunciation if locale changes (WCAG 3.1.1) |

These are tracked as individual issues in the Audit Remediation project, milestone *Audit — UI/UX & Accessibility*.

## Not yet verified

Automated axe/Lighthouse scans, screen-reader testing, keyboard-only journey tests, zoom/reflow at 400%, colour-contrast measurement and mobile device testing. [Testing strategy](TESTING_STRATEGY.md) states browser-based accessibility testing is incomplete.

## Feedback

Report accessibility barriers through the [support guide](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/.github/SUPPORT.md) or a security/accessibility issue. Do not include personal data in reports.

Related: [UI review](UI_REVIEW.md) · [Development guidelines](DEVELOPMENT_GUIDELINES.md)
