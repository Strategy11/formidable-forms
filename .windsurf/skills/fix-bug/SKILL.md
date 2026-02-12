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

## Workflow

### Phase 1: Understand

- Read and understand the complete issue
- Clarify expected vs actual behavior
- Identify reproduction steps
- Determine scope: which plugin(s), which feature(s)

### Phase 2: Locate

- Use code_search to find the root cause (not just symptoms)
- Trace execution flow from entry point to failure
- **Analyze complete context** of the class or file being changed — all features, logic, and flows
- **Trace parent hierarchy**: search parent classes and files up to plugin root
- Identify ALL affected locations in the codebase
- Map dependencies: what calls this code, what does this code call
- Check plugin requirements: must code work standalone or require Pro/addons

### Phase 3: Research

- Find existing patterns: search models, controllers, helpers, views for similar functionality
- Study pattern usage: search ALL places using the pattern
- Search official WordPress/VIP docs: function parameters, return types, deprecated alternatives
- Search platform-specific docs: performance and security best practices
- Verify alignment: ensure approach matches existing codebase patterns
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

- Make the smallest change that completely solves the problem
- Never change method signatures, return types, or data structures
- Never refactor unrelated code in the same commit
- Add defensive checks where data comes in, not where it is used everywhere
- Follow WordPress PHP/JS coding standards and Formidable naming patterns

### Phase 6: Verify

- Confirm fix resolves the reported issue
- Test with Pro plugin active AND inactive
- Test with empty data and missing keys
- Confirm no PHP warnings, notices, or errors in any scenario
- Confirm backward compatibility with existing callers
- Confirm fix is testable without touching other code
- Remove any debug code and verify code style
- Run through [checklist.md](checklist.md)

### Phase 7: Report

Output a single concise report following [report-template.md](report-template.md).

The report contains **all** deliverables in one place:

```markdown
Report
├── Root Cause → Fix  (1 sentence each)
├── Files Changed     (file path + what changed)
├── PR Info
│   ├── Branch        (fix/{issue}-{slug})
│   ├── PR Title      (human-readable, NOT conventional commit format)
│   ├── PR Body       (Fixes #N + description + test steps)
│   └── Commit Msg    (conventional commit, NO issue number)
└── Manual Test Steps (numbered reproduction/verification)
```

**Critical rules**: see [pr-template.md](pr-template.md) for details:

- **PR title** = plain English summary (e.g. `Fix dropdown hidden behind panel`)
- **PR body** = where `Fixes #ISSUE` goes + description + test steps
- **Commit message** = conventional commit format, body explains *what/why*, NO issue ref

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
