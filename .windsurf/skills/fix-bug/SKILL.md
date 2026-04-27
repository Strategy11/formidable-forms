---
name: fix-bug
description: Bug-fixing workflow for Formidable Forms. Use when fixing bugs, debugging unexpected behavior, investigating error logs, or resolving compatibility issues.
---

# Fix Bug

Structured bug-fixing workflow for the Formidable Forms plugin ecosystem, following WordPress, Formidable Forms, and WordPress VIP coding standards.

This skill builds on the always-on rules in `.windsurf/rules/enterprise/` which define core principles, code change phases, writing style, and commit message format. Those rules apply automatically to every conversation. This skill extends them with bug-fix-specific steps.

## When to Use

- Fixing reported bugs
- Debugging unexpected behavior
- Investigating error logs
- Resolving compatibility issues

---

## Coding Standards

Before writing or modifying ANY code, read and follow the applicable rules from `.windsurf/rules/`:

| File type                | Rules to read                                                                                                        |
| ------------------------ | -------------------------------------------------------------------------------------------------------------------- |
| `*.php`                  | `formidable/frm-php.md`, `wordpress/php.md`, `wordpress-vip/wpvip-security.md`, `wordpress-vip/wpvip-performance.md` |
| `*.js`, `*.jsx`, `*.mjs` | `formidable/frm-javascript.md`, `wordpress/javascript.md`, `wordpress-vip/wpvip-security.md`                         |
| `*.css`, `*.scss`        | `formidable/frm-css.md`                                                                                              |
| `*.html`                 | `wordpress/html.md`                                                                                                  |
| Block editor code        | `wordpress/block-editor.md`, `wordpress-vip/wpvip-block-editor.md`                                                   |
| UI/forms/user-facing     | `wordpress/accessibility.md`                                                                                         |
| Tests                    | `formidable/testing.md`                                                                                              |

**How to apply:**

1. Before implementing, read ALL rules that match the file types you will modify
2. Follow every rule when writing new code or modifying existing code

---

## Workflow

The always-on rule `enterprise/code-change-principles.md` defines the core phases: Understand, Locate, Research, Select Solution, Implement, and Verify. The steps below are **additional** bug-fix-specific requirements.

### Phase 4: Design

- Document proposed solutions using [solution-template.md](solution-template.md)

### Phase 6: Verify

- Run through [checklist.md](checklist.md)

### Phase 7: Report

Output a single concise report following [report-template.md](report-template.md).

The report contains all deliverables:

```markdown
Report
├── Root Cause → Fix (1 sentence each)
├── Files Changed (file path + what changed)
├── PR Info
│ ├── Branch (fix/{issue}-{slug})
│ ├── PR Title (human-readable, NOT conventional commit format)
│ ├── PR Body (Fixes URL + description + test steps)
│ └── Commit Msg (conventional commit, NO issue number)
└── Manual Test Steps (numbered reproduction/verification)
```

See [pr-template.md](pr-template.md) for PR title, PR body, and commit message formatting rules.

---

## Supporting Resources

- [checklist.md](checklist.md): Verification checklist
- [solution-template.md](solution-template.md): Solution comparison template
- [report-template.md](report-template.md): Completion report template
- [pr-template.md](pr-template.md): Branch name, PR title, and PR body template

## Invocation

Cascade automatically invokes this skill when your request matches bug-fixing tasks.

To manually invoke:

```text
@fix-bug
```
