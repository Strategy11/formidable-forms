# PR Template

> Three things to output: **branch**, **PR title + body**, **commit message**.
> PR title is not the same as commit message. They follow different rules.

---

## Branch

```text
fix/{issue-number}-{short-slug}
```

- 2-4 word kebab-case slug
- Examples: `fix/1234-date-validation`, `fix/5678-export-timeout`

---

## PR Title

**Plain English. NOT conventional commit format.**

| Rule           | Detail                                      |
|----------------|---------------------------------------------|
| Format         | Human-readable sentence fragment            |
| Capitalization | Sentence case (first word capitalized)      |
| Length         | ≤ 72 characters                             |
| Mood           | Imperative ("Fix", not "Fixed" or "Fixes")  |
| No period      | Do not end with `.`                         |

**Examples:**

- `Fix dropdown hidden behind panel in form builder`
- `Prevent date field validation error for non-US formats`
- `Escape HTML entities in field labels`

**Wrong** (do NOT use conventional commit format for PR titles):

- ~~`fix(builder): prevent dropdown clipping at edge`~~
- ~~`fix(fields): resolve date validation error`~~

---

## PR Body

The PR body **must** contain the issue reference and a testing section.

**Do NOT hard-wrap PR body text.** Write natural paragraphs and let GitHub handle line wrapping. The 72-char wrap rule only applies to **commit message bodies**, not PR bodies.

```markdown
Fixes {full_github_issue_url}

[1-2 sentence description of the fix. Do NOT hard-wrap lines.]

## Testing

1. [Reproduction / verification step]
2. [Expected result after fix]
```

**Issue reference format:** Always use the full GitHub issue URL (e.g., `https://github.com/Strategy11/formidable-pro/issues/3030`) instead of `#number`, because the PR may target a different repo than the issue.

---

## Commit Message

Follows **Conventional Commits**: separate from PR title.

```text
type(scope): subject (imperative mood, ≤ 50 chars)

[Optional body: what changed and why. Wrap at 72 chars.]
```

| Rule               | Detail                                       |
|--------------------|----------------------------------------------|
| Subject ≤ 50 chars | Lowercase after `type(scope):`               |
| Body wraps at 72   | Explains *what* and *why*, not *how*         |
| No issue ref       | `Fixes #N` goes in the **PR body**, not here |
| No period          | Subject line does not end with `.`           |

**Scopes:** builder, entries, fields, api, admin, frontend, db, i18n, security, deps

**Examples:**

```text
fix(builder): prevent dropdown clipping at edge

When a row has many fields, the field action dropdown
extends beyond the container boundary and gets clipped.
Add a left-edge overflow check to reposition it.
```

```text
fix(fields): handle non-US date format validation

Date field was rejecting valid dates in dd/mm/yyyy
format. Use locale-aware parsing instead.
```
