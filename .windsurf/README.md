# Windsurf Configuration for Formidable Forms

This directory contains comprehensive Windsurf configuration files for Formidable Forms development following WordPress VIP standards.

## How It All Works Together

Windsurf has **four mechanisms** for customizing Cascade behavior. Here's how they interact:

| Mechanism     | Purpose                       | Trigger                                   | Best For                             |
| ------------- | ----------------------------- | ----------------------------------------- | ------------------------------------ |
| **Rules**     | Behavioral guidelines         | Always-on, glob, model decision, manual   | Coding style, project conventions    |
| **Skills**    | Complex tasks with resources  | Auto (progressive disclosure) or @mention | Multi-step workflows, reference docs |
| **Workflows** | Repetitive task sequences     | /slash-command                            | Deployment, testing, bug fixing      |
| **AGENTS.md** | Directory-scoped instructions | Auto based on file location               | Location-specific conventions        |

### Activation Flow

1. **AGENTS.md** at root applies to ALL files (always on)
2. **Rules** with `trigger: always_on` apply to every conversation
3. **Rules** with `trigger: glob` apply when matching files are involved
4. **Skills** auto-invoke based on task relevance (via description matching)
5. **Workflows** invoke explicitly via `/command`

## Directory Structure

```
.windsurf/
├── README.md                          # This file
├── AGENTS.md                          # Root-level project guidelines (always applies)
├── rules/
│   ├── conventional-commits.md        # Commit message format (always_on)
│   ├── formidable-core.md             # Core development rules (always_on)
│   ├── php-wordpress.md               # PHP/WordPress standards (*.php glob)
│   └── web-search-policy.md           # Mandatory web search rules (always_on)
├── skills/
│   ├── add-field-type/                # Guide for creating new field types
│   │   └── SKILL.md
│   ├── code-review/                   # Code review checklist and guidelines
│   │   └── SKILL.md
│   ├── wordpress-php-coding-standards/
│   │   ├── SKILL.md                   # Skill definition with frontmatter
│   │   ├── AGENTS.md                  # Full reference (supporting file)
│   │   └── rules/                     # Detailed rule files
│   ├── wordpress-css-coding-standards/
│   ├── wordpress-javascript-coding-standards/
│   ├── wordpress-html-coding-standards/
│   └── wordpress-accessibility-coding-standards/
└── workflows/
    ├── fix-bug.md                     # /fix-bug workflow
    ├── phpcs-check.md                 # /phpcs-check workflow
    ├── run-tests.md                   # /run-tests workflow
    └── security-audit.md              # /security-audit workflow
```

## Rules (Automatic Application)

Rules are automatically applied based on their trigger:

| Rule                      | Trigger       | When Active             |
| ------------------------- | ------------- | ----------------------- |
| `conventional-commits.md` | `always_on`   | Every conversation      |
| `formidable-core.md`      | `always_on`   | Every conversation      |
| `web-search-policy.md`    | `always_on`   | Every conversation      |
| `php-wordpress.md`        | `glob: *.php` | When PHP files involved |

## Skills (Progressive Disclosure + Manual)

Skills are invoked in two ways:

### Automatic Invocation

Cascade reads the skill's `description` field and automatically invokes when relevant:

- Working with PHP files → `wordpress-php-coding-standards` may auto-invoke
- Working with CSS files → `wordpress-css-coding-standards` may auto-invoke
- Creating new field types → `add-field-type` may auto-invoke

### Manual Invocation

Type `@skill-name` in Cascade:

- `@wordpress-php-coding-standards` - Full PHP standards reference
- `@wordpress-css-coding-standards` - CSS standards reference
- `@wordpress-javascript-coding-standards` - JavaScript standards reference
- `@wordpress-html-coding-standards` - HTML standards reference
- `@wordpress-accessibility-coding-standards` - WCAG 2.2 Level AA standards
- `@add-field-type` - Step-by-step field type creation
- `@code-review` - Code review checklist

### Why Skills Have AGENTS.md Files

The `AGENTS.md` files inside skill folders are **supporting resources**, not directory-scoped rules. When a skill is invoked, Cascade can read these comprehensive reference documents.

## Workflows (Slash Commands)

Invoke workflows using slash commands:

| Command           | Purpose                        |
| ----------------- | ------------------------------ |
| `/fix-bug`        | Structured bug fixing workflow |
| `/run-tests`      | Run PHPUnit tests              |
| `/phpcs-check`    | Run PHP CodeSniffer            |
| `/security-audit` | Security audit checklist       |

### Skills Apply During Workflows

When using `/fix-bug` on a PHP file, Cascade will:

1. Follow the workflow steps
2. Auto-invoke `wordpress-php-coding-standards` if relevant
3. Apply all `always_on` rules (formidable-core, web-search-policy, conventional-commits)

## Ensuring WordPress Standards Always Apply

To ensure WordPress coding standards always apply:

### Option 1: Let Auto-Invocation Work (Recommended)

The skill descriptions are crafted to trigger when working with relevant file types:

- "Apply when working with PHP files" → triggers for PHP work
- "Apply when working with CSS files" → triggers for CSS work

### Option 2: Explicit @mention

Add `@wordpress-php-coding-standards` to your prompt when you want explicit standards enforcement.

### Option 3: Add to Workflow

Edit workflows to include skill references:

```markdown
## Phase 5: Implement the Fix

Follow @wordpress-php-coding-standards for all code changes.
```

## Commit Messages

All commits must follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/):

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

**Types:** `fix`, `feat`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`

**Scopes:** `builder`, `entries`, `fields`, `api`, `admin`, `frontend`, `db`, `i18n`, `security`, `deps`

**Examples:**

```
fix(fields): resolve date field validation error
feat(builder): add drag-and-drop field reordering
refactor(entries)!: change entry meta storage format
```

## Lint Warnings

The markdown lint warnings (MD033, MD040) in these files are **intentional**:

- XML-style tags (`<security>`, `<phpdoc>`) help Cascade parse grouped rules
- Code blocks without language specs are for commit message examples

These do not affect Windsurf functionality.

## Related Documentation

- [Windsurf Memories & Rules](https://docs.windsurf.com/windsurf/cascade/memories)
- [Windsurf Skills](https://docs.windsurf.com/windsurf/cascade/skills)
- [Windsurf Workflows](https://docs.windsurf.com/windsurf/cascade/workflows)
- [Windsurf AGENTS.md](https://docs.windsurf.com/windsurf/cascade/agents-md)
- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress VIP Documentation](https://docs.wpvip.com/)
