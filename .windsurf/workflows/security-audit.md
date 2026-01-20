---
name: security-audit
description: Perform security audit on Formidable Forms code following WordPress VIP standards
---

# Security Audit Workflow

## Step 1: Identify Scope

Determine what to audit:

- Specific file or function
- Recent changes (PR or commit)
- Entire feature area

## Step 2: Input Handling Audit

Check ALL user input sources:

```php
// Sources to check:
$_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE
// Formidable helpers:
FrmAppHelper::get_param()
FrmAppHelper::get_post_param()
FrmAppHelper::simple_get()
```

For each input, verify:

- [ ] Sanitized with appropriate function
- [ ] Type validated where needed
- [ ] Default values are safe

## Step 3: Output Escaping Audit

Check ALL output locations:

| Context        | Required Function  |
| -------------- | ------------------ |
| HTML text      | `esc_html()`       |
| HTML attribute | `esc_attr()`       |
| URL            | `esc_url()`        |
| JavaScript     | `esc_js()`         |
| Textarea       | `esc_textarea()`   |
| JSON           | `wp_json_encode()` |

Verify:

- [ ] Every echo/print is escaped
- [ ] Correct function for context
- [ ] No raw user data in output

## Step 4: SQL Injection Audit

Check ALL database queries:

```php
// MUST use prepare() for:
$wpdb->query()
$wpdb->get_results()
$wpdb->get_row()
$wpdb->get_var()
$wpdb->get_col()
```

Verify:

- [ ] All queries use `$wpdb->prepare()`
- [ ] Correct placeholders used (%d, %s, %f)
- [ ] Table names properly prefixed
- [ ] LIMIT clauses present

## Step 5: AJAX Security Audit

For each AJAX handler:

```php
// Required checks:
check_ajax_referer( 'frm_ajax', 'nonce' );
if ( ! current_user_can( 'capability' ) ) {
    wp_die( -1 );
}
```

Verify:

- [ ] Nonce verification present
- [ ] Capability check present
- [ ] Proper error response on failure

## Step 6: File Operation Audit

Check file operations:

- [ ] No direct file includes with user input
- [ ] File uploads validated (type, size, content)
- [ ] WP_Filesystem used where appropriate
- [ ] No arbitrary file reads/writes

## Step 7: Report Findings

Generate security report:

```markdown
## Security Audit Report: [Target]

### Critical Issues (Fix Immediately)

- [ ] Issue description with file:line

### High Priority Issues

- [ ] Issue description with file:line

### Medium Priority Issues

- [ ] Issue description with file:line

### Recommendations

1. [Specific recommendation]
2. [Specific recommendation]
```
