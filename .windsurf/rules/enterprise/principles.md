---
trigger: always_on
description: Enterprise code change principles for safe, minimal-scope modifications.
---

# Enterprise Code Change Principles

Critical principles for enterprise-level plugin development.

---

## Core Principles

1. **NEVER guess** — Always search and verify before making changes
2. **Minimal scope** — Fix at the most specific location, closest to the problem
3. **Backward compatibility** — Maintain 100% compatibility with existing callers
4. **No custom solutions** — Never invent new patterns; use existing ones
5. **User changes are final** — If user makes manual changes, treat as authoritative

---

## Analysis Phase

Before proposing solutions:

1. **Read and understand** the complete issue
2. **Identify ALL affected locations** in the codebase
3. **Map dependencies** — What calls this code? What does this code call?
4. **Check plugin requirements** — Must code work standalone or require dependencies?

---

## Solution Selection

- Propose 2-3 solutions with trade-offs clearly stated
- Choose solution with minimal scope and lowest risk
- Fix at the most specific location
- Prefer adding safety checks over refactoring
- **Verify changes are not overkill** — If affecting several areas, analyze all to ensure fix is not excessive

---

## Change Execution

- Make the smallest change that completely solves the problem
- Never change method signatures, return types, or data structures
- Never refactor unrelated code in the same commit
- Add defensive checks where data comes in, not where used everywhere

---

## Mandatory Research

Before ANY code change involving platform functions:

1. **Search codebase first** — Understand existing patterns
2. **Search official docs** — Function parameters, return types, deprecated alternatives
3. **Search platform-specific docs** — Performance and security best practices
4. **Verify alignment** — Ensure approach matches existing patterns

---

## Change Verification Checklist

Before completing any change:

- [ ] Does this change break any existing functionality?
- [ ] Does this work with all plugin configurations?
- [ ] Are there warnings/errors in any scenario?
- [ ] Is the change backward compatible?
- [ ] Have you searched docs for best practices?
- [ ] Can this be tested without touching other code?
