---
trigger: always_on
description: Enforces conventional commit message format for all git commits in the project.
---

# Conventional Commit Messages

All commit messages MUST follow the Conventional Commits 1.0.0 specification.

## Format

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

## Types

| Type       | Description                                           | SemVer |
| ---------- | ----------------------------------------------------- | ------ |
| `fix`      | Bug fix (patches a bug in the codebase)               | PATCH  |
| `feat`     | New feature (adds functionality to the codebase)      | MINOR  |
| `docs`     | Documentation only changes                            | -      |
| `style`    | Code style changes (formatting, whitespace, etc.)     | -      |
| `refactor` | Code change that neither fixes a bug nor adds feature | -      |
| `perf`     | Performance improvement                               | -      |
| `test`     | Adding or correcting tests                            | -      |
| `build`    | Changes to build system or external dependencies      | -      |
| `ci`       | CI configuration changes                              | -      |
| `chore`    | Other changes that don't modify src or test files     | -      |

## Scope (Optional)

The scope provides additional context about what part of the codebase the commit affects:

- `builder` - Form builder related changes
- `entries` - Entry management changes
- `fields` - Field types or field handling
- `api` - API endpoints or integrations
- `admin` - Admin UI changes
- `frontend` - Front-end form display
- `db` - Database operations
- `i18n` - Internationalization
- `security` - Security fixes or improvements
- `deps` - Dependencies

## Breaking Changes

Indicate breaking changes with:

1. `!` after type/scope: `feat(api)!: change response format`
2. `BREAKING CHANGE:` footer in the commit body

## Examples

```
fix(fields): resolve date field validation error

The date field was incorrectly validating dates in non-US formats.
Added locale-aware date parsing.

Fixes #1234
```

```
feat(builder): add drag-and-drop field reordering

Implements smooth drag-and-drop functionality for reordering fields
in the form builder interface.
```

```
fix: prevent XSS in field labels

Escape HTML entities in field labels before output.
```

```
refactor(entries)!: change entry meta storage format

BREAKING CHANGE: Entry meta now uses JSON encoding instead of
serialized PHP arrays. Run migration script before updating.
```

## Rules

1. Type MUST be lowercase.
2. Description MUST start with lowercase letter.
3. Description MUST NOT end with a period.
4. Description MUST be imperative mood ("add" not "added" or "adds").
5. Body MUST be separated from description by a blank line.
6. Breaking changes MUST be indicated with `!` or `BREAKING CHANGE:` footer.
7. Keep description under 72 characters.

## AI Commit Message Generation

When generating commit messages:

1. Analyze the staged changes to determine the appropriate type.
2. Identify the scope from the files/directories changed.
3. Write a concise description in imperative mood.
4. Add body with details if the change is complex.
5. Include issue references if mentioned in the conversation.
