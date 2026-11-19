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

## Findings from the 2026-11-19 static audit — remediated

All findings below were fixed in PR #40 and their issues closed:

| Area | Fix |
| --- | --- |
| Skip-to-content link missing | Added a skip link and `id="main-content"` on both `<main>` elements (WCAG 2.4.1) |
| Dark mode on summary/catalogue/notification views | Tailwind `dark:` variant now follows `body[data-theme]`; dark backgrounds/borders/text added (WCAG 1.4.3) |
| Theme applied after paint; toggle label starts wrong | Inline pre-paint theme script, `color-scheme`, `theme-color` meta and a theme-agnostic initial label (WCAG 1.4.3 / 4.1.2) |
| Validation errors not programmatically tied to most fields | `aria-invalid` / `aria-describedby` applied generically and first invalid control focused (WCAG 3.3.1) |
| Live search filters have no `aria-live` region | Polite live region announces match counts (WCAG 4.1.3) |
| Several tables missing `scope` and captions | Added `scope="col"` and screen-reader captions (WCAG 1.3.1) |
| Images lack intrinsic dimensions | `width`/`height` added to all images |
| "Forgot password?" is a non-focusable span | Replaced with a disabled button plus a visible explanation (WCAG 2.1.1) |
| `lang="en"` hardcoded on authenticated pages | Now follows the application locale (WCAG 3.1.1) |

## Not yet verified

Automated axe/Lighthouse scans, screen-reader testing, keyboard-only journey tests, zoom/reflow at 400%, colour-contrast measurement and mobile device testing. [Testing strategy](TESTING_STRATEGY.md) states browser-based accessibility testing is incomplete.

## Feedback

Report accessibility barriers through the [support guide](https://github.com/PraiseTechzw/CUT-ICT-HELPDESK/blob/main/.github/SUPPORT.md) or a security/accessibility issue. Do not include personal data in reports.

Related: [UI review](UI_REVIEW.md) · [Development guidelines](DEVELOPMENT_GUIDELINES.md)
