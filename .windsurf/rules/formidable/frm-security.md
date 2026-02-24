---
trigger: glob
globs: ['**/*.php', '**/*.js']
description: Formidable Forms security patterns and helper functions. Auto-applies to PHP and JS files.
---

# Formidable Forms Security Patterns

Formidable-specific security helpers and patterns that extend WordPress VIP security standards.

---

## Input Handling

### Use Formidable Input Helpers

Prefer these Formidable functions over accessing `$_POST` or `$_GET` directly:

| Function | Use Case |
| -------- | -------- |
| `FrmAppHelper::get_post_param( $param, $default, $sanitize )` | Read from `$_POST` |
| `FrmAppHelper::simple_get( $param, $sanitize, $default )` | Read from `$_GET` |
| `FrmAppHelper::get_param( $param, $default, $src, $sanitize )` | Read from `$_GET` or `$_POST` |

**Important:** You must explicitly specify the `$sanitize` parameter or the value will not be sanitized.

### Examples

```php
// Read from $_POST with sanitization
$title = FrmAppHelper::get_post_param( 'title', '', 'sanitize_text_field' );
$email = FrmAppHelper::get_post_param( 'email', '', 'sanitize_email' );
$url   = FrmAppHelper::get_post_param( 'url', '', 'sanitize_url' );
$key   = FrmAppHelper::get_post_param( 'key', '', 'sanitize_key' );

// Read from $_GET with sanitization
$page = FrmAppHelper::simple_get( 'page', 'sanitize_text_field' );

// Read from either $_GET or $_POST (checks both)
$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
```

### Common Sanitization Functions

| Function | Use Case |
| -------- | -------- |
| `sanitize_text_field()` | Single line text |
| `sanitize_textarea_field()` | Multi-line text |
| `sanitize_email()` | Email addresses |
| `sanitize_file_name()` | File names |
| `sanitize_key()` | Keys and slugs |
| `sanitize_title()` | Titles and slugs |
| `sanitize_url()` | URLs |
| `absint()` | Positive integers |
| `intval()` | Integers |
| `wp_kses()` | HTML with allowed tags |

---

## Nonce Verification

Use Formidable helpers to read nonce values:

```php
// In form
wp_nonce_field( 'my_action', 'my_nonce' );

// On submission: Use Formidable helper
$nonce = FrmAppHelper::get_post_param( 'my_nonce', '', 'sanitize_text_field' );
if ( ! $nonce || ! wp_verify_nonce( $nonce, 'my_action' ) ) {
	wp_die( 'Security check failed' );
}
```

---

## Serialization

### serialize() vs unserialize()

- `serialize()` is safe to use
- `unserialize()` has object injection risk: **never use on user data**

### Safe Alternatives

```php
// CORRECT: Use Formidable helper
$data = FrmAppHelper::maybe_unserialize_array( $serialized_data );

// CORRECT: Use JSON instead
$data = json_decode( $json_string, true );

// INCORRECT: Security risk
$data = unserialize( $user_input );
```

---

## DOM Sanitization (JavaScript)

Use `frmDom.cleanNode` for sanitizing DOM nodes. Do **not** introduce DOMPurify as a dependency.

```javascript
// CORRECT: Use existing Formidable helper
frmDom.cleanNode( element );

// INCORRECT: Do not add new dependencies
import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize( userData );
```

### Unsafe JavaScript Patterns

```javascript
// UNSAFE: Never use
element.innerHTML = userData;
$( element ).html( userData );
eval( userInput );

// SAFE: Programmatic DOM manipulation
const text = document.createTextNode( userData );
element.appendChild( text );
```

---

## Authorization

### Capability Checks

Formidable provides helper methods for permission checks:

```php
// Check if user has a Formidable capability
if ( ! FrmAppHelper::current_user_can( 'frm_edit_forms' ) ) {
	wp_die( esc_html( FrmAppHelper::get_settings()->admin_permission ) );
}

// Check permission and nonce together
$error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'frm_ajax', 'nonce' );
if ( $error ) {
	wp_die( esc_html( $error ) );
}

// Check if user has any of multiple roles
if ( ! FrmAppHelper::user_has_permission( array( 'frm_edit_forms', 'frm_view_forms' ) ) ) {
	wp_die( 'Unauthorized access' );
}
```

### Formidable Capabilities

| Capability | Description |
| ---------- | ----------- |
| `frm_view_forms` | View forms list |
| `frm_edit_forms` | Create and edit forms |
| `frm_delete_forms` | Delete forms |
| `frm_change_settings` | Modify plugin settings |
| `frm_view_entries` | View form entries |
| `frm_edit_entries` | Edit form entries |
| `frm_delete_entries` | Delete form entries |

---

## Forbidden Functions

| Function | Reason | Alternative |
| -------- | ------ | ----------- |
| `unserialize()` | Object injection risk | `FrmAppHelper::maybe_unserialize_array()` or `json_decode()` |
| Direct `$_POST`/`$_GET` | Unsanitized input | Use `FrmAppHelper::get_post_param()`, `simple_get()`, `get_param()` |
