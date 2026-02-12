# PR Template

## Branch Name

**Format:** `fix/{issue-number}-{short-description}`

Use 2-4 word kebab-case description specific to the issue.

**Examples:**

- `fix/1234-date-validation-error`
- `fix/5678-entry-export-timeout`
- `fix/910-field-label-xss`

---

## PR Title

Follow Conventional Commits format:

```text
fix(scope): brief description
```

**Rules:**

- 50 characters or fewer
- Imperative mood ("fix", not "fixed")
- Lowercase after type/scope
- No period at end

**Scopes:** builder, entries, fields, api, admin, frontend, db, i18n, security, deps

**Examples:**

- `fix(fields): resolve date validation error`
- `fix(entries): prevent export timeout on large data`
- `fix(security): escape HTML in field labels`

---

## PR Body

```markdown
Fixes #{issue_number}

Brief description of what this PR fixes.

### Testing

- Clarify the steps to reproduce the issue
- Verify that the fix fully resolves the issue
```
