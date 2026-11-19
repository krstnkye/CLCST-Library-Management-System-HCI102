# Universal Toast Notifications Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace routine action-result modals with accessible, non-blocking toast notifications throughout every portal.

**Architecture:** Laravel continues to expose action results through the shared flash partial. `public/js/ui-actions.js` hydrates those results into a single toast host, while validation errors remain persistent inline alerts. The existing CLCST styles are extended rather than replaced.

**Tech Stack:** Laravel Blade, vanilla JavaScript, CSS, PHPUnit feature tests

**Spec:** `docs/superpowers/specs/2026-11-19-universal-toast-notifications-design.md`

## Global Constraints

- Reuse existing `status`, `status_title`, `error_title`, and `status_quiet` session data.
- Do not add third-party dependencies.
- Success toasts dismiss after about five seconds and pause while hovered or focused.
- Validation and security errors remain inline and persistent.
- Respect reduced-motion preferences and mobile navigation spacing.

## Review Focus

- A quiet sign-in greeting must not create a toast.
- Restoring a page through browser history must not replay a toast.
- Multiple messages must stack without overwriting each other.
- Keyboard users must be able to focus and dismiss a toast.
- Markup used inside a message must be escaped before insertion.

---

### Task 1: Shared flash contract

**Files:**
- Modify: `tests/Feature/InteractionTest.php`
- Modify: `resources/views/partials/flash.blade.php`

**Interfaces:**
- Produces: server-rendered `[data-flash]` elements with title, tone, message, and optional quiet state.

- [ ] **Step 1: Write the failing feature test**

Add `test_action_status_exposes_accessible_toast_data_without_a_result_modal_contract` and assert that a normal status renders `data-flash="success"`, its title and message, while the partial itself contains no modal trigger.

- [ ] **Step 2: Run the focused test and verify RED**

Run: `php artisan test --compact tests/Feature/InteractionTest.php`

Expected: FAIL because the new toast-specific contract is missing.

- [ ] **Step 3: Implement the minimal shared flash markup**

Keep the visible inline error block. Mark normal success output for toast hydration, keep quiet status as a non-toast inline notice, and ensure the message remains escaped by Blade.

- [ ] **Step 4: Run the focused test and verify GREEN**

Run: `php artisan test --compact tests/Feature/InteractionTest.php`

Expected: PASS.

### Task 2: Accessible toast behavior and styling

**Files:**
- Modify: `tests/Feature/SystemAuditTest.php`
- Modify: `public/js/ui-actions.js`
- Modify: `public/css/ui-actions.css`

**Interfaces:**
- Consumes: `[data-flash]` markup from Task 1.
- Produces: `clcstUI.toast(message, options)` with title, tone, close control, timed dismissal, pause/resume, stacking, and reduced-motion-safe transitions.

- [ ] **Step 1: Write the failing source-contract tests**

Add focused assertions for a toast close button, `aria-live`, pause/resume events, title handling, a five-second lifetime, and the absence of `modal(` inside `flashResult`.

- [ ] **Step 2: Run the focused test and verify RED**

Run: `php artisan test --compact tests/Feature/SystemAuditTest.php`

Expected: FAIL because the current toast lacks the required interaction contract and flash results still open a modal.

- [ ] **Step 3: Implement the toast API and flash hydration**

Change `toast(message, tone)` to accept an options object while preserving existing calls. Add title text, dismiss button, focusable toast, pause/resume timing, and deterministic cleanup. Change `flashResult()` to call `toast()` for successful non-quiet flashes and leave errors inline.

- [ ] **Step 4: Refine responsive and reduced-motion CSS**

Keep the existing dark CLCST surface, add title/message hierarchy and a visible focus/close state, place mobile toasts above navigation, and disable transitions for reduced motion.

- [ ] **Step 5: Run focused tests and verify GREEN**

Run: `php artisan test --compact tests/Feature/SystemAuditTest.php tests/Feature/InteractionTest.php`

Expected: PASS.

### Task 3: Verification and integration

**Files:**
- Modify if needed: `README.md`

**Interfaces:**
- Consumes: completed toast behavior.
- Produces: verified system behavior and updated project documentation.

- [ ] **Step 1: Update the README interaction description**

Describe confirmations as modal dialogs and successful results as toast notifications.

- [ ] **Step 2: Run the complete suite**

Run: `php artisan test --compact`

Expected: all tests pass.

- [ ] **Step 3: Run design and source checks**

Run the Impeccable detector once over `resources/views/partials/flash.blade.php`, `public/js/ui-actions.js`, and `public/css/ui-actions.css`, then run `git diff --check`.

- [ ] **Step 4: Verify in the browser**

Trigger one successful record update, confirm the toast appears and dismisses, test keyboard close and mobile placement, and confirm zero browser-console errors.

