---
name: fix-bug
description: Structured workflow for fixing bugs in Formidable Forms following enterprise practices
---

# Fix Bug Workflow

## Phase 1: Analysis

1. **Understand the bug report:**
   - What is the expected behavior?
   - What is the actual behavior?
   - Steps to reproduce

2. **Search the codebase:**
   - Find the relevant code using `grep_search` or `code_search`
   - Identify ALL affected locations
   - Map dependencies (what calls this, what does this call)

3. **Check Pro compatibility:**
   - Does this bug affect Lite, Pro, or both?
   - Will the fix work when Pro is inactive?

## Phase 2: Research

4. **Search WordPress docs** (if using WP functions):

   ```
   @web WordPress [function_name] parameters best practices
   ```

5. **Search VIP docs** (if performance/security related):

   ```
   @web WordPress VIP [topic] best practices
   ```

6. **Check existing patterns:**
   - How is similar functionality handled elsewhere in the codebase?
   - Are there helper methods that should be used?

## Phase 3: Solution Design

7. **Propose 2-3 solutions** with trade-offs:
   - Solution A: [Description] - Pros/Cons
   - Solution B: [Description] - Pros/Cons
   - Recommended: [Choice with reasoning]

8. **Verify minimal scope:**
   - Fix at the most specific location
   - Avoid unnecessary refactoring
   - Preserve backward compatibility

## Phase 4: Implementation

9. **Make the fix:**
   - Apply the smallest change that solves the problem
   - Add defensive checks where needed
   - Follow coding standards

10. **Add/update tests:**
    - Write test that reproduces the bug
    - Verify test fails before fix
    - Verify test passes after fix

## Phase 5: Verification

11. **Run verification checks:**
    - [ ] PHPCS passes
    - [ ] PHPUnit tests pass
    - [ ] No PHP warnings/notices
    - [ ] Works when Pro is inactive
    - [ ] Backward compatible

12. **Report completion:**
    - Summary of the fix
    - Files changed
    - Any remaining concerns
