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

### Phase 1: Understand

- Read and understand the complete issue
- Clarify expected vs actual behavior
- Identify reproduction steps
- Determine scope: which plugin(s), which feature(s)
- **Cross-plugin**: Identify if the feature or setting exists in Lite, Pro, or both. Understand the full feature context across all relevant plugins
    - Working on an addon: research must include Lite AND Pro
    - Working on Pro: research must include Lite
    - Working on Lite: check if the change affects Pro or any active addon

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

- **Never invent custom solutions if existing patterns exist**
- Find existing patterns: search models, controllers, helpers, views for similar functionality
- Study pattern usage: search ALL places using the pattern
- Search official WordPress/VIP docs: function parameters, return types, deprecated alternatives
- Search platform-specific docs: performance and security best practices
- Verify alignment: ensure approach matches existing codebase patterns
- **Cross-plugin**: Search for existing patterns and helpers in Lite and Pro that already solve the problem. Reuse them instead of inventing new solutions
- **Iterate**: if a better pattern is found, repeat from Phase 2

---

## Solution Phase

### Phase 4: Select Solution

- Propose 2-3 solutions with trade-offs clearly stated
- Select the solution with minimal scope and lowest risk
- Fix at the most specific location, closest to root cause
- Prefer adding safety checks over refactoring
- Fix must be testable without touching other code
- **Verify changes are not overkill**: If affecting several areas, review everything carefully to make sure the fix is not excessive. If it is, go back to Phase 2

---

## Change Phase

### Phase 5: Implement

- Never refactor unrelated code in the same commit
- If a rule conflicts with existing code in the file being modified, follow the rule for new code but do not refactor unrelated existing code
- Make the smallest change that completely solves the problem
- Never change method signatures, return types, or data structures
- Add defensive checks where data comes in, not where used everywhere
- Add PHPDoc/JSDoc for new methods/properties/functions and comments for complex logic
- Never guess the version number and use `@since x.x` as the version placeholder in docblocks

---

## Change Verification Phase

### Phase 6: Verify

- Confirm fix resolves the reported issue
- Confirm backward compatibility with existing callers
- Confirm this change does not break any existing functionality
- Test with Pro plugin active AND inactive
- Test with empty data and missing keys
- Confirm no PHP warnings, notices, or errors in any scenario
- Test edge cases
- Remove all debug code (error_log statements, debug comments, debug files)
- Verify code passes phpcs/linting checks
