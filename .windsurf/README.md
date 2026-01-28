# Windsurf Configuration for Formidable Forms

This directory contains comprehensive Windsurf configuration files for Formidable Forms development.

## Installation

Copy the contents to your Formidable plugins directory:

```bash
# Copy to formidable-master (Lite)
cp -r rules/ /path/to/formidable-master/.windsurf/rules/
cp -r skills/ /path/to/formidable-master/.windsurf/skills/
cp -r workflows/ /path/to/formidable-master/.windsurf/workflows/
cp AGENTS.md /path/to/formidable-master/AGENTS.md

# Copy to formidable-pro-master (Pro)
cp -r rules/ /path/to/formidable-pro-master/.windsurf/rules/
cp -r skills/ /path/to/formidable-pro-master/.windsurf/skills/
cp -r workflows/ /path/to/formidable-pro-master/.windsurf/workflows/
cp AGENTS.md /path/to/formidable-pro-master/AGENTS.md
```

## Directory Structure

```
windsurf-formidable/
├── README.md                          # This file
├── AGENTS.md                          # Root-level project guidelines
├── rules/
│   ├── formidable-core.md             # Core development rules (always_on)
│   ├── php-wordpress.md               # PHP/WordPress standards (*.php glob)
│   └── web-search-policy.md           # Mandatory web search rules (always_on)
├── skills/
│   ├── add-field-type/
│   │   └── SKILL.md                   # Guide for creating new field types
│   └── code-review/
│       └── SKILL.md                   # Code review checklist and guidelines
└── workflows/
    ├── run-tests.md                   # /run-tests workflow
    ├── phpcs-check.md                 # /phpcs-check workflow
    ├── fix-bug.md                     # /fix-bug workflow
    └── security-audit.md              # /security-audit workflow
```

## Usage

### Rules (Automatic)

Rules are automatically applied based on their trigger:

- **formidable-core.md** - Always active, enforces core development practices
- **php-wordpress.md** - Active for all PHP files
- **web-search-policy.md** - Always active, ensures web searches for best practices

### Skills (Manual or Automatic)

Invoke skills by typing `@skill-name` or let Cascade auto-invoke based on context:

- `@add-field-type` - Guides creation of new field types
- `@code-review` - Performs comprehensive code review

### Workflows (Slash Commands)

Invoke workflows using slash commands:

- `/run-tests` - Run PHPUnit tests
- `/phpcs-check` - Run PHP CodeSniffer
- `/fix-bug` - Structured bug fixing workflow
- `/security-audit` - Security audit checklist

## Key Features

### 1. Mandatory Web Search

The rules enforce searching WordPress docs before:

- Using any WordPress function
- Writing database queries
- Implementing security measures
- Making performance optimizations

### 2. WordPress VIP Compliance

All rules align with WordPress VIP coding standards:

- Prepared SQL queries
- Input sanitization
- Output escaping
- Performance optimization

### 3. Formidable Patterns

Rules enforce Formidable-specific patterns:

- Class naming conventions (Frm*, FrmPro*)
- Hook naming conventions (frm*\*, frm_pro*\*)
- Factory pattern usage
- Helper method usage

### 4. Pro Compatibility

All changes must work:

- When Pro plugin is active
- When Pro plugin is inactive
- With empty/null data
- With missing configuration

## Lint Warnings

The markdown lint warnings (MD033, MD040) in these files are **intentional**:

- XML-style tags (`<security>`, `<phpdoc>`) help Cascade parse grouped rules
- Some code blocks without language specs are for search query examples

These do not affect Windsurf functionality.

## Customization

Feel free to modify these files for your specific needs:

1. **Add new rules** - Create new `.md` files in `rules/`
2. **Add new skills** - Create new directories in `skills/` with `SKILL.md`
3. **Add new workflows** - Create new `.md` files in `workflows/`

## Related Documentation

- [Windsurf Memories & Rules](https://docs.windsurf.com/windsurf/cascade/memories)
- [Windsurf Skills](https://docs.windsurf.com/windsurf/cascade/skills)
- [Windsurf Workflows](https://docs.windsurf.com/windsurf/cascade/workflows)
- [Windsurf AGENTS.md](https://docs.windsurf.com/windsurf/cascade/agents-md)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress VIP Documentation](https://docs.wpvip.com/)
