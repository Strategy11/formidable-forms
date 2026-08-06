---
name: formidable-labs-skill-index
description: >
  Routing index for the Strategy11 Labs plugin development skills. Load when
  writing, restructuring, or reviewing code for a Formidable Forms add-on or a
  Strategy11 Labs plugin (Form Revisions, Email Options, Field Tooltips) and
  it isn't already obvious which specialist skill applies. Maps task type to the
  specific skills worth loading. Not needed for questions, single-file edits, or
  when the right skill is already known.
---

# Strategy11 Labs — Skill Routing Index

Maps a task to the smallest set of skills that covers it.

---

## Fast path — skip the index

Go straight to the work, loading at most the one skill that obviously applies:

- Answering a question about how something works
- Reading or explaining existing code
- A one-line or single-function fix in a plugin already understood
- Version bumps, readme edits, changelog entries, packaging a zip
- Anything not touching Formidable add-on code at all

Routing exists to keep large builds correct, not to gate small work. If the task
is small, treat everything below as optional reference.

---

## Before writing new add-on code

Worth loading for anything beyond a small edit, roughly in this order:

| Order | Skill | Why |
|---|---|---|
| 1 | `frm-bootstrap` | Plugin loading sequence, capability checks, admin page detection |
| 2 | `frm-gotchas` | Known traps — cheap to read, expensive to rediscover |
| 3 | `frm-house-style` | Formidable in-house style (MVC classes/, naming, autoloader, HooksController) — required for review acceptance |

Then add the task-specific skills below.

---

## Load by Task Type

### Building a new Formidable add-on (general)
- `wp-plugin-standards` — security, performance, JS, compatibility, testing, wporg

### Form builder saves, field hooks, per-form settings
- `frm-hooks-builder`

### Entries: create, update, validate, delete
- `frm-hooks-entry-lifecycle` + `frm-db-helpers`

### Form actions (email, custom action types, action settings UI)
- `frm-actions-system`

### Email notifications specifically
- `frm-email-hooks` + `frm-actions-system`

### Settings pages (global or per-form)
- `frm-hooks-settings`

### Reading/writing DB data
- `frm-db-helpers` + `frm-data-structures`

### Field type checks or field_options access
- `frm-field-types`

### Writing or reviewing tests
- `frm-testing`

### Custom tab on the Confirmation action / frontend submit triggers
- `frm-confirmation-tab`

### Browser-based testing of a built plugin
- `wp-browser-testing` — post-install verification, form submit, debug.log reading

### Something is broken / user reports a bug
- `failure-analysis` — load this IMMEDIATELY, before looking at any code

### Complete new plugin from scratch
- Start with `wp-plugin-standards`, then pull in specialist skills as each
  feature area comes up. Loading all of them up front costs ~100 KB of context
  before a line is written and most of it won't apply — let the build pull them.

---

## Skill Summary

| Skill | Loads when... |
|---|---|
| `frm-bootstrap` | Any hook registration or plugin init |
| `frm-data-structures` | Reading/writing DB, table schema, options keys |
| `frm-hooks-builder` | Builder saves, form options, field update hooks |
| `frm-hooks-entry-lifecycle` | Entry validate/create/update/delete |
| `frm-hooks-settings` | Global or per-form settings pages |
| `frm-actions-system` | Custom action types, action UI, get_field_name() |
| `frm-email-hooks` | Email notification hooks, email_key |
| `frm-db-helpers` | FrmEntry, FrmField, FrmEntryMeta, FrmDb methods |
| `frm-field-types` | Field type strings, field_options keys |
| `frm-gotchas` | Before ANY code — known wrong assumptions |
| `frm-testing` | Writing/reviewing tests, regression guards |
| `failure-analysis` | Any reported bug or unexpected behaviour |
| `wp-plugin-standards` | Security, performance, JS, wporg rules |
| `frm-confirmation-tab` | Custom tab on Confirmation action; frontend submit triggers |
| `wp-browser-testing` | Browser automation to test a built plugin |
| `frm-house-style` | In-house structure/naming/loading conventions for review acceptance |

---

## Build Workflow — Phase Order

### Phase 1 — Plan
Before writing any code, answer:
- What hooks does this need? (list them; load the relevant hook skill)
- What DB reads/writes? Can any be avoided?
- What settings? Where stored?
- What capability gates each feature?

### Phase 2 — File Structure
Formidable house structure (see `frm-house-style` for the full rules):
`my-plugin.php` (thin bootstrap: autoloader + frm_load_controllers),
`readme.txt`, `uninstall.php`, `composer.json` (classmap `classes/`),
`classes/{controllers,models,helpers,views}/`, `assets/`, `tests/`.
Class files are StudlyCaps, filename = class name, no `class-` prefix.

### Phase 3 — Write Code
Follow `wp-plugin-standards`. Load specialist skills for every hook used.

### Phase 3.5 — Generate and Write Tests (mandatory before delivery)

```bash
python3 /path/to/skill/scripts/generate_tests.py /home/claude/my-plugin --is-formidable
cd /home/claude/my-plugin && composer install
composer test
```

The generator writes **real tests** for:
- Every AJAX handler — 3 tests each: bad nonce, bad capability, success path
- Every hook registration — one assertion per registered hook
- Every detected Formidable filter callback — return-type assertions
- All Formidable regression guards — known bug patterns from frm-gotchas

Stubs (`markTestIncomplete`) are only written for business logic that requires
context. Fill those in before delivery.

**No plugin is packaged with failing tests. Incomplete tests are acceptable at
first release. Failing tests are not.**

### Phase 4 — Self-Review Checklist
See `wp-plugin-standards`.

### Phase 4.5 — Automated Review

```bash
bash /path/to/skill/scripts/review.sh /home/claude/my-plugin --is-formidable
```

All 5 checks must pass: PHP lint → PHPCS → PHPStan → PHPUnit → API review.

### Phase 5 — Package

```bash
cd /home/claude && zip -r my-plugin-alpha-X.Y.Z.zip my-plugin/ \
  --exclude "*/vendor/*" --exclude "*/node_modules/*" --exclude "*/.git/*"
```

Bump version in plugin header + `MY_PLUGIN_VERSION` constant + `readme.txt` Stable tag.

---

## Failure Protocol

When Nathanael reports something is not working, load `failure-analysis` and
work through all five phases before touching code:

1. Write a failing test that reproduces the bug
2. Identify root cause category
3. Identify why the wrong approach was selected
4. Fix; confirm test passes; confirm full suite still green
5. Update `frm-gotchas`, the relevant specialist skill, and `frm-testing`

Report format after every fix:
```
BUG: [what didn't work]
ROOT CAUSE: [one sentence]
WHY WRONG APPROACH WAS CHOSEN: [honest one sentence]
WHAT PREVENTS RECURRENCE: [test added / skill updated / gotcha added]
STATUS: All tests passing, review.sh clean.
```

**Goal: every failure makes the next project safer. Recurring bugs mean Phase 5 was skipped.**

---

## Strategy11 Labs Plugin Header

```php
/**
 * Plugin Name:       My Plugin
 * Plugin URI:        https://labs.formidableforms.com
 * Description:       Proof of concept Formidable Labs creation that allows you to [what it does]. Report bugs at labs.formidableforms.com
 * Version:           Alpha 1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Strategy11 Labs
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-plugin
 */
```

- Author: `Strategy11 Labs`
- Plugin URI: `https://labs.formidableforms.com`
- Version: prefixed `Alpha`
- Description: starts with `Proof of concept Formidable Labs creation that allows you to`
- Description: ends with `Report bugs at labs.formidableforms.com`
- Bump plugin header + constant + readme.txt on every change
- ZIP: `my-plugin-alpha-X.Y.Z.zip`


<!-- skills-sync: 2026-08-06 skill-language-reframe -->
