---
trigger: always_on
description: Enterprise code change principles for safe, minimal-scope modifications.
---

# Enterprise Code Change Principles

Critical principles for enterprise-level plugin development.

---

## Core Principles

1. **NEVER guess**: Always search and verify before making changes
2. **Minimal scope**: Fix at the most specific location, closest to the problem
3. **Backward compatibility**: Maintain 100% compatibility with existing callers
4. **No custom solutions**: Never invent new patterns. Use existing ones or search the web to review best practices, then follow the official WordPress standards or VIP guidelines.
5. **User changes are final**: If user makes manual changes, treat as authoritative

---

## Analysis Phase

Before proposing solutions:

1. **Read and understand** the complete issue
2. **Find existing patterns**: Search models, controllers, helpers, views for similar functionality
3. **Study pattern usage**: Search ALL places using the pattern
4. **Trace parent hierarchy**: Search parent files up to plugin root
5. **Use Fast Context for codebase awareness**: Use the code_search tool to understand all relevant code locations, dependencies, and related functionality before making changes
6. **Analyze complete context**: Completely analyze the class or file being changed to understand all context around features, logic, and flows for complete understanding
7. **Identify ALL affected locations** in the codebase
8. **Map dependencies**: What calls this code? What does this code call?
9. **Check plugin requirements**: Must code work standalone or require dependencies?
10. **Iterate if needed**: If a better pattern is found, repeat from step 2

**Never invent custom solutions if existing patterns exist.**

---

## Solution Selection

- Propose 2-3 solutions with trade-offs clearly stated
- Choose solution with minimal scope and lowest risk
- Fix at the most specific location
- Prefer adding safety checks over refactoring
- **Verify changes are not overkill**: If affecting several areas, analyze all to ensure fix is not excessive

---

## Change Execution

- Make the smallest change that completely solves the problem
- Never change method signatures, return types, or data structures
- Never refactor unrelated code in the same commit
- Add defensive checks where data comes in, not where used everywhere

---

## Mandatory Research

Before ANY code change involving platform functions:

1. **Search codebase first**: Understand existing patterns
2. **Search official docs**: Function parameters, return types, deprecated alternatives
3. **Search platform-specific docs**: Performance and security best practices
4. **Verify alignment**: Ensure approach matches existing patterns

---

## Writing Style for Generated Text

Applies to all generated text: PR titles, PR body, commit messages, branch names.

- **No em dashes** (`—` or `–`): use commas, periods, or rewrite the sentence
- **No semicolons** (`;`): split into separate sentences instead
- **Full GitHub URL** for issue references (e.g., `https://github.com/Strategy11/formidable-pro/issues/3030`), never shorthand `#number` (PRs may target a different repo than the issue)
- **No hard-wrapping** in PR body text: let GitHub handle line wrapping. The 72-char wrap rule applies only to **commit message bodies**

---

## Change Verification Checklist

Before completing any change:

- [ ] Does this change break any existing functionality?
- [ ] Does this work with all plugin configurations?
- [ ] Are there warnings/errors in any scenario?
- [ ] Is the change backward compatible?
- [ ] Have you searched docs for best practices?
- [ ] Can this be tested without touching other code?
