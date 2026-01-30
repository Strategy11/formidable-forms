---
name: code-review
description: Performs thorough code review following Formidable and WordPress VIP standards
---

# Code Review Guidelines

Use this skill to review code changes for Formidable Forms.

## Review Process

### 1. Security Review

Check for these security issues:

- [ ] **SQL Injection** - All queries use `$wpdb->prepare()`
- [ ] **XSS** - All output escaped with appropriate `esc_*` function
- [ ] **CSRF** - AJAX handlers verify nonce
- [ ] **Authorization** - Capability checks in place
- [ ] **Input Sanitization** - All user input sanitized

### 2. WordPress VIP Compliance

- [ ] No direct database queries without prepare()
- [ ] No `extract()`, `eval()`, or `create_function()`
- [ ] Query results limited with LIMIT
- [ ] No `file_get_contents()` for remote URLs
- [ ] Expensive operations cached

### 3. Formidable Standards

- [ ] Class naming follows `Frm` or `FrmPro` prefix
- [ ] Hook naming follows `frm_` or `frm_pro_` prefix
- [ ] Correct text domain used
- [ ] Factory pattern used for field types
- [ ] Helper methods used where available

### 4. Code Quality

- [ ] Functions under 100 lines
- [ ] Cyclomatic complexity under 10
- [ ] Line length under 180 characters
- [ ] No code duplication
- [ ] Early returns used
- [ ] Clear variable naming

### 5. Documentation

- [ ] PHPDoc for all public methods
- [ ] `@since` tags for new methods
- [ ] `{@inheritDoc}` for overridden methods
- [ ] Comments end with periods
- [ ] Translators comments for sprintf

### 6. Compatibility

- [ ] Works when Pro inactive
- [ ] Backward compatible
- [ ] No PHP warnings/notices
- [ ] Handles empty/null values

## Common Issues to Flag

```php
// BAD: Direct query
$wpdb->query( "DELETE FROM ... WHERE id = $id" );

// GOOD: Prepared query
$wpdb->query( $wpdb->prepare( "DELETE FROM ... WHERE id = %d", $id ) );

// BAD: Unescaped output
echo $user_input;

// GOOD: Escaped output
echo esc_html( $user_input );

// BAD: No nonce check
public static function ajax_handler() {
    // Handler code...
}

// GOOD: Nonce verified
public static function ajax_handler() {
    check_ajax_referer( 'frm_ajax', 'nonce' );
    // Handler code...
}
```

## Review Output Format

```markdown
## Code Review: [File/PR Name]

### Security

- ✅ SQL injection protection verified
- ⚠️ Missing escaping at line X

### VIP Compliance

- ✅ All queries prepared
- ❌ Missing LIMIT on query at line Y

### Standards

- ✅ Naming conventions followed
- ⚠️ Function exceeds 100 lines

### Recommendations

1. Add escaping at line X
2. Add LIMIT to query at line Y
3. Consider extracting method for lines A-B
```
