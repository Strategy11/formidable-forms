---
trigger: always_on
description: Mandatory web search policy for WordPress and Formidable development.
---

# Web Search Policy

## Mandatory Searches

You MUST search the web in these scenarios:

### 1. WordPress Functions

Before using ANY WordPress function you're not 100% certain about:

```
@web WordPress [function_name] parameters return type
```

### 2. Database Operations

Before writing any database query:

```
@web WordPress VIP database query best practices
@web WordPress $wpdb prepare examples
```

### 3. Security Functions

Before using sanitization or escaping:

```
@web WordPress sanitize_text_field vs sanitize_textarea_field
@web WordPress esc_html vs esc_attr when to use
```

### 4. Deprecated Functions

When you see a function that might be deprecated:

```
@web WordPress [function_name] deprecated alternative
```

### 5. Hooks and Actions

Before adding new hooks:

```
@web WordPress hook naming conventions best practices
```

### 6. Performance Critical Code

For loops, queries, or frequently executed code:

```
@web WordPress VIP performance optimization [topic]
```

## Search Sources Priority

1. **developer.wordpress.org** - Official WordPress documentation.
2. **docs.wpvip.com** - WordPress VIP best practices.
3. **developer.wordpress.org/plugins** - Plugin development handbook.
4. **make.wordpress.org/core** - Core development decisions.

## Quick Reference URLs

When searching, prioritize these docs:

- PHP Standards: `developer.wordpress.org/coding-standards/wordpress-coding-standards/php/`
- Security: `developer.wordpress.org/plugins/security/`
- Database: `developer.wordpress.org/plugins/database/`
- VIP Code Standards: `docs.wpvip.com/technical-references/code-review/`
- VIP Performance: `docs.wpvip.com/technical-references/caching/`

## Search Before Action

ALWAYS search BEFORE:

- Adding a new WordPress function call.
- Writing a database query.
- Implementing security measures.
- Making performance optimizations.
- Adding new hooks or filters.

The cost of searching is minimal compared to the cost of getting it wrong.
