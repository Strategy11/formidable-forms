---
description: Enterprise bug-fixing workflow for Formidable Forms following WordPress VIP standards
---

# Fix Bug Workflow

## Phase 1: Understand the Bug

- **Clarify the issue:**
  - What is the expected behavior?
  - What is the actual behavior?
  - What are the exact steps to reproduce?
  - Does the user have sample data, screenshots, or logs?

- **Determine scope:**
  - Does this affect Lite, Pro, or both?
  - Which add-ons are involved (Views, Surveys, Signature, etc.)?
  - Is this a regression or a long-standing issue?

## Phase 2: Locate the Problem

- **Search for the root cause:**

```text
Use code_search to find relevant code based on the bug description.
```

- **Map the execution flow:**
  - Trace the code path from trigger to symptom
  - Identify ALL files and methods involved
  - Document: what calls this code? What does this code call?

- **Add diagnostic logging** (if flow is unclear):
  - Add temporary `error_log()` statements at key decision points
  - Include variable values, method names, and timestamps
  - Use unique prefixes like `[FRM_DEBUG_BUGNAME]` for easy grep

- **Identify the root cause:**
  - Find the EXACT line/condition where behavior diverges
  - Understand WHY it fails (not just WHERE)
  - Document the root cause before proceeding

## Phase 3: Analyze All Fix Locations

- **List ALL possible fix locations:**
  - Where could this be fixed? (may be multiple places)
  - Which location is closest to the root cause?
  - Which location has the smallest blast radius?

- **Search for existing patterns:**

```text
grep_search for similar functionality in the codebase.
Look for helper methods in FrmAppHelper, FrmDb, FrmFieldsHelper.
Check how similar issues were solved elsewhere.
```

- **Research WordPress best practices:**

```text
@web WordPress [relevant_function] developer documentation
@web WordPress VIP [topic] best practices
```

## Phase 4: Design the Solution

- **Propose 2-3 solutions** with clear trade-offs:

| Solution | Description          | Pros       | Cons    |
| -------- | -------------------- | ---------- | ------- |
| A        | [Fix at location X]  | [Benefits] | [Risks] |
| B        | [Fix at location Y]  | [Benefits] | [Risks] |
| C        | [Different approach] | [Benefits] | [Risks] |

- **Select the best solution based on:**
  - Minimal scope (smallest change that solves the problem)
  - Closest to root cause (not downstream workaround)
  - Follows existing Formidable patterns
  - Maintains backward compatibility
  - Works with Pro active AND inactive

- **Get user approval** before implementing if:
  - Multiple valid approaches exist
  - Solution requires significant changes
  - Trade-offs are not obvious

## Phase 5: Implement the Fix

- **Follow Formidable coding standards:**
  - Use `elseif` not `else if`
  - Use strict comparisons (`===`, `!==`)
  - Use `in_array()` with third parameter `true`
  - Tabs for indentation
  - Max 180 character line length

- **Follow WordPress VIP standards:**
  - ALL user input must be sanitized
  - ALL output must be escaped
  - ALL database queries must use `$wpdb->prepare()`
  - NEVER use `extract()`, `eval()`, or `create_function()`

- **Make the minimal fix:**
  - Change ONLY what is necessary
  - Do NOT refactor unrelated code
  - Do NOT change method signatures unless required
  - Add defensive checks where data enters, not everywhere

- **Add PHPDoc if adding new methods:**

```php
/**
 * Brief description ending with period.
 *
 * @since x.x
 *
 * @param type $param Description.
 * @return type Description.
 */
```

## Phase 6: Clean Up

- **Remove all debug code:**
  - Remove ALL `error_log()` statements
  - Remove ALL temporary comments
  - Ensure no debug files were created

- **Verify code style:**

```bash
vendor/bin/phpcs --standard=phpcs.xml [changed_files]
```

## Phase 7: Test the Fix

- **Create or update tests:**

```bash
vendor/bin/phpunit tests/phpunit/[relevant_test_file].php
```

- **Manual verification checklist:**
  - [ ] Bug is fixed in reported scenario
  - [ ] No PHP warnings or notices
  - [ ] Works when Pro plugin is ACTIVE
  - [ ] Works when Pro plugin is INACTIVE
  - [ ] Does not break existing functionality
  - [ ] Backward compatible with existing data

## Phase 8: Report Completion

- **Summarize the fix:**
  - **Root cause:** [One sentence explaining why the bug occurred]
  - **Solution:** [One sentence explaining the fix]
  - **Files changed:** [List of modified files]
  - **Testing done:** [What was verified]
  - **Remaining concerns:** [Any edge cases or follow-ups]

---

<important_reminders>

- NEVER guess - always search and verify before making changes
- Fix at the MOST SPECIFIC location, closest to the problem
- Maintain 100% backward compatibility
- Code must work with AND without Pro active
- Remove ALL debug code before completion

</important_reminders>
