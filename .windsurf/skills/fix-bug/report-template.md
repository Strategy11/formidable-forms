# Bug Fix Report Template

> Fill each placeholder. Keep every section short.

---

## Root Cause

[1-2 sentences: what is broken and why]

## Fix

[1-2 sentences: what you changed to fix it]

## Files Changed

- `path/to/file` — [what changed]

## PR Info

- **Branch:** `fix/{issue-number}-{short-slug}`
- **PR Title:** [Human-readable summary — plain English, no conventional commit prefix]
- **PR Body:**

```markdown
Fixes #{issue_number}

[1-2 sentence description of the fix]

## Testing

1. [Step to reproduce / verify]
2. [Expected result after fix]
```

- **Commit Message:**

```text
type(scope): subject line (imperative, ≤50 chars)

[Optional body: what changed and why. Wrap at 72 chars.
Do NOT put issue references here — they go in the PR body.]
```

## Manual Test Steps

1. [Step]
2. [Step]
3. [Expected result]
