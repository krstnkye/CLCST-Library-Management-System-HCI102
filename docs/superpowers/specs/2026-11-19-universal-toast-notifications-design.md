# Universal Toast Notifications — Design

## Intent

Give every student, faculty, librarian, administrator, and super-administrator immediate, consistent feedback after records are added, updated, or removed. Routine confirmations should be noticeable without interrupting the user’s workflow.

## Goals

- Convert ordinary successful action feedback into compact toast notifications.
- Reuse Laravel’s existing `status` and `status_title` flash data, so current controller actions gain the behavior automatically.
- Show a meaningful title and message, with success and error presentation that matches the existing CLCST visual system.
- Support close, automatic dismissal, pause on pointer hover or keyboard focus, stacked messages, screen-reader announcements, and reduced motion.
- Work consistently at desktop and mobile sizes without covering primary navigation.

## Non-goals

- Do not change controllers merely to trigger the toast when they already return standard flash status data.
- Do not turn validation or security errors into easy-to-miss, auto-dismissing feedback.
- Do not redesign the dashboard visual language or introduce a third-party notification library.

## Chosen approach

Use the existing shared flash partial and `clcstUI.toast()` infrastructure as the single integration point. Routine success flash messages will render as an accessible toast instead of opening the existing large result modal. Validation errors remain inline and persistent; they may also receive a non-auto-dismissing error toast only when useful, without replacing the field-level explanation.

This approach was selected over showing both banners and toasts, which would duplicate feedback, and over editing each controller separately, which would be inconsistent and difficult to maintain.

## Interaction details

- Toast content: contextual title followed by the action result message.
- Success icon: check mark; error icon: error indicator.
- Placement: lower-right on desktop and above mobile navigation on small screens.
- Lifetime: approximately five seconds for success; timer pauses while hovered or focused.
- Controls: visible dismiss button with an accessible label.
- Motion: short entrance and exit transitions; disabled when reduced motion is requested.
- Multiple results: stack newest notifications without replacing earlier ones.
- Browser back/forward restoration: do not replay an already-seen flash result.

## Verification

- Feature test confirms shared flash markup exposes title, message, tone, and quiet-state behavior.
- JavaScript behavior test or source contract confirms toast hydration, close control, timer pause, and no success modal invocation.
- Existing role and controller tests continue to pass.
- Manual checks cover one add/update/remove action, keyboard dismissal, mobile placement, and browser console errors.

