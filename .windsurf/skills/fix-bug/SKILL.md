---
name: fix-bug
description: Enterprise bug-fixing workflow for Formidable Forms following WordPress VIP standards. Use when fixing bugs or debugging issues.
---

# Fix Bug Skill

Enterprise bug-fixing workflow for Formidable Forms following WordPress VIP standards.

## When to Use

- Fixing reported bugs
- Debugging unexpected behavior
- Investigating error logs
- Resolving compatibility issues

## Core Principles

1. **NEVER guess**: Always search and verify before making changes
2. **Minimal scope**: Fix at the most specific location, closest to the problem
3. **Backward compatibility**: Maintain 100% compatibility with existing callers
4. **No custom solutions**: Use existing patterns or follow official WordPress/VIP standards
5. **User changes are final**: If user makes manual changes, treat as authoritative

---

## Mandatory: Coding Standards

Before writing or modifying ANY code, you MUST read and follow the applicable coding standards rules from `.windsurf/rules/`. These rules are the authoritative source for code style, syntax, and patterns.

**Rules location:** `.windsurf/rules/` (relative to the formidable-master plugin root)

**Rules by file type:**

| File type                | Rules to read                                                                  |
| ------------------------ | ------------------------------------------------------------------------------ |
| `*.php`                  | `wordpress/php.md`, `formidable/frm-php.md`, `wordpress-vip/wpvip-security.md` |
| `*.js`, `*.jsx`, `*.mjs` | `wordpress/javascript.md`, `wordpress-vip/wpvip-security.md`                   |
| `*.css`, `*.scss`        | `formidable/frm-css.md`                                                        |
| `*.html`                 | `wordpress/html.md`                                                            |
| Block editor code        | `wordpress/block-editor.md`                                                    |
| UI/forms/user-facing     | `wordpress/accessibility.md`                                                   |
| Tests                    | `formidable/testing.md`                                                        |
| Commit messages          | `enterprise/conventional-commits.md`                                           |
| General principles       | `enterprise/principles.md`                                                     |

**How to apply:**

1. Before Phase 5 (Implement), read ALL rules that match the file types you will modify
2. Follow every rule in those files when writing new code or modifying existing code
3. If a rule conflicts with existing code in the file being modified, follow the rule for new code but do not refactor unrelated existing code
4. Use `@since x.x` as the version placeholder in docblocks (never guess the version number)

---

## Cross-Plugin Research

Formidable Forms is a multi-plugin ecosystem. When working on any plugin in this ecosystem, you MUST also research the related plugins to understand the full context.

**Rules:**

- **Working on an addon** (e.g., formidable-woocommerce, formidable-views, formidable-dates): Research must include formidable (Lite) AND formidable-pro. The feature, setting, or code path being fixed almost always has counterparts or dependencies in Lite and Pro.
- **Working on formidable-pro**: Research must include formidable (Lite). Pro extends Lite, so every Pro feature builds on Lite infrastructure.
- **Working on formidable (Lite)**: Check if the change affects Pro or any active addon.

**Apply this in Phases 1, 2, and 3.** When tracing execution flow, mapping dependencies, or finding existing patterns, always search across the relevant plugins, not just the plugin being modified.

---

## Workflow

### Phase 1: Understand

- Read and understand the complete issue
- Clarify expected vs actual behavior
- Identify reproduction steps
- Determine scope: which plugin(s), which feature(s)
- **Cross-plugin**: Identify if the feature or setting exists in Lite, Pro, or both. Understand the full feature context across all relevant plugins

### Phase 2: Locate

- Use code_search to find the root cause (not just symptoms)
- Trace execution flow from entry point to failure
- **Analyze complete context** of the class or file being changed — all features, logic, and flows
- **Trace parent hierarchy**: search parent classes and files up to plugin root
- Identify ALL affected locations in the codebase
- Map dependencies: what calls this code, what does this code call
- Check plugin requirements: must code work standalone or require Pro/addons
- **Cross-plugin**: Search for the same feature, helper, or code path in Lite and Pro (and addons if relevant). Understand how data flows between plugins

### Phase 3: Research

- Find existing patterns: search models, controllers, helpers, views for similar functionality
- Study pattern usage: search ALL places using the pattern
- Search official WordPress/VIP docs: function parameters, return types, deprecated alternatives
- Search platform-specific docs: performance and security best practices
- Verify alignment: ensure approach matches existing codebase patterns
- **Cross-plugin**: Search for existing patterns and helpers in Lite and Pro that already solve the problem. Reuse them instead of inventing new solutions
- **Never invent custom solutions if existing patterns exist**
- **Iterate**: if a better pattern is found, repeat from Phase 2

### Phase 4: Design

- Propose 2-3 solutions with trade-offs clearly stated
- Select the solution with minimal scope and lowest risk
- Fix at the most specific location, closest to root cause
- Prefer adding safety checks over refactoring
- Verify the fix is not overkill: if affecting multiple areas, analyze all to ensure it is not excessive
- Document with [solution-template.md](solution-template.md)

### Phase 5: Implement

- **Read all applicable coding standards rules** from `.windsurf/rules/` before writing any code (see Mandatory: Coding Standards section above)
- Make the smallest change that completely solves the problem
- Never change method signatures, return types, or data structures
- Never refactor unrelated code in the same commit
- Add defensive checks where data comes in, not where it is used everywhere
- Follow the coding standards rules strictly: correct syntax, naming, formatting, and patterns for each file type

### Phase 6: Verify

- Confirm fix resolves the reported issue
- Test with Pro plugin active AND inactive
- Test with empty data and missing keys
- Confirm no PHP warnings, notices, or errors in any scenario
- Confirm backward compatibility with existing callers
- Confirm fix is testable without touching other code
- Remove any debug code and verify code style
- **Verify code follows all applicable `.windsurf/rules/`**: correct variable declarations, naming conventions, formatting, and patterns
- Run through [checklist.md](checklist.md)

### Phase 7: Report

Output a single concise report following [report-template.md](report-template.md).

The report contains **all** deliverables in one place:

```markdown
Report
├── Root Cause → Fix (1 sentence each)
├── Files Changed (file path + what changed)
├── PR Info
│ ├── Branch (fix/{issue}-{slug})
│ ├── PR Title (human-readable, NOT conventional commit format)
│ ├── PR Body (Fixes #N + description + test steps)
│ └── Commit Msg (conventional commit, NO issue number)
└── Manual Test Steps (numbered reproduction/verification)
```

**Critical rules**: see [pr-template.md](pr-template.md) for details:

- **PR title** = plain English summary (e.g. `Fix dropdown hidden behind panel`)
- **PR body** = where `Fixes {full_github_issue_url}` goes + description + test steps (no hard-wrapping)
- **Commit message** = conventional commit format, body explains _what/why_, NO issue ref

**Writing style** (applies to PR titles, PR body, and commit messages):

- **No em dashes** (`—` or `–`): use commas, periods, or rewrite the sentence
- **No semicolons** (`;`): split into separate sentences instead
- **No hard-wrapping** in PR body text: let GitHub handle line wrapping (72-char wrap is for commit bodies only)
- **Full GitHub URL** for issue references (e.g., `https://github.com/Strategy11/formidable-pro/issues/3030`), not `#number`

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
