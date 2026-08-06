---
name: strategy11-labs-tools
description: >
  Automated review and test generation scripts for Strategy11 Labs plugins.
  Load when running the automated review pipeline, generating unit tests, or
  performing an independent API code review. Contains review.sh (full 5-check
  pipeline), generate_tests.py (PHPUnit test generator), api_review.py
  (independent Claude review), phpcs.xml (WordPress coding standards ruleset),
  and phpstan.neon (static analysis config). Scripts live in the scripts/
  subfolder of this skill. Reference paths from other skills point here.
---

# Strategy11 Labs — Automated Tools

This skill contains the executable scripts used in the build workflow. Run them
via bash as documented below — the invocations here are the supported interface,
so reading the source is normally unnecessary. Do read it when a script fails in
a way the docs don't explain, or when changing what a script does.

---

## Script Reference

| Script | Purpose |
|---|---|
| `scripts/review.sh` | Full 5-check pipeline: PHP lint → PHPCS → PHPStan → PHPUnit → API review |
| `scripts/generate_tests.py` | Generates real PHPUnit tests from plugin source (v2 — not stubs) |
| `scripts/api_review.py` | Independent Claude review via Anthropic API (called by review.sh) |
| `scripts/phpcs.xml` | PHPCS ruleset: WordPress-Extra + security sniffs |
| `scripts/phpstan.neon` | PHPStan config: level 5, WordPress stubs |

---

## Phase 3.5 — Generate Tests

Run immediately after writing plugin code, before the automated review.

```bash
# Formidable Forms add-on:
python3 {skill_path}/scripts/generate_tests.py /home/claude/my-plugin --is-formidable

# Standalone WordPress plugin:
python3 {skill_path}/scripts/generate_tests.py /home/claude/my-plugin

# Then install dev dependencies and run:
cd /home/claude/my-plugin && composer install
composer test
```

The generator writes **real tests** (not stubs) for:
- Every AJAX handler — 3 tests: bad nonce, bad capability, success path
- Every hook registration — one assertion per registered hook
- Every detected Formidable filter callback — return-type assertions
- All Formidable regression guards — known bug patterns from frm-gotchas

Only business logic that requires context produces `markTestIncomplete` stubs.
Fill those in before delivery. **No plugin ships with failing tests.**

---

## Phase 4.5 — Automated Review

Run after Phase 3.5 tests pass. All 5 checks must pass before packaging.

```bash
# Formidable Forms add-on:
bash {skill_path}/scripts/review.sh /home/claude/my-plugin --is-formidable

# Standalone plugin:
bash {skill_path}/scripts/review.sh /home/claude/my-plugin

# Skip API review for quick local runs:
bash {skill_path}/scripts/review.sh /home/claude/my-plugin --is-formidable --skip-api
```

### What each check catches

| Check | Catches |
|---|---|
| PHP lint | Syntax errors, parse errors |
| PHPCS | Unescaped output, missing sanitization, coding standards |
| PHPStan | Type errors, undefined variables, wrong argument counts |
| PHPUnit | Failing tests, regressions from previous bugs |
| API review | Logic issues, architectural problems, header compliance, and lead-reviewer style conventions (negative-leading conditionals, double-escaping after `array_to_html_params`, uncast `apply_filters()` returns, jQuery-over-vanilla, inaccurate docblock types) |

Lint coverage: every shipped PHP/JS file must be covered by phpcs/eslint — do
not add files that escape the linters.

### Exit codes
- `0` — all checks passed, safe to package
- `1` — one or more checks failed, do not package

---

## Replacing `{skill_path}`

When referencing scripts in commands, replace `{skill_path}` with the actual
path to this skill in the container. The skill loader exposes this as the
directory containing this SKILL.md file. In bash, find it with:

```bash
# The scripts are always in a scripts/ subfolder of this skill's directory
TOOLS_DIR=$(dirname "$(find /mnt/skills -name 'strategy11-labs-tools' -type d 2>/dev/null | head -1)")/strategy11-labs-tools
bash "$TOOLS_DIR/scripts/review.sh" /home/claude/my-plugin --is-formidable
```

Or use the known container path directly:
```bash
bash /mnt/skills/user/strategy11-labs-tools/scripts/review.sh /home/claude/my-plugin --is-formidable
python3 /mnt/skills/user/strategy11-labs-tools/scripts/generate_tests.py /home/claude/my-plugin --is-formidable
```


---

## Deploy + browser test workflow

### Current workflow (one manual step)

```
1. [server] review/lint → zip
2. [server] present_files → user clicks download            ← ONE MANUAL STEP
3. [browser] navigate to WP admin → upload plugin → activate
4. [browser] run the automated test suite (see wp-browser-testing)
```

### Why step 2 is manual

There is no direct server→browser or browser→server file path, and the browser
can't drive the WP plugin-upload form, so the zip goes out via `present_files`
and Nathanael installs it. From there the browser handles the rest.

A GitHub release zip URL *is* fetchable by the browser (github.com /
raw.githubusercontent.com are reachable), which makes for a near-hands-off
deploy when a release is published.

For the test snippets themselves — probe style, reading the site's debug.log,
download pacing — see `wp-browser-testing`. Don't duplicate them here.

<!-- skills-sync: 2026-08-06 skill-language-reframe -->
