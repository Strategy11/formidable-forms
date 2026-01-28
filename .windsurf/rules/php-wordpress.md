---
trigger: glob
globs: ["*.php"]
description: PHP and WordPress coding standards for Formidable Forms development.
---

# PHP & WordPress Standards

## Database Operations

### Query Patterns

```php
// CORRECT - Use prepared statements
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}frm_items WHERE form_id = %d",
        $form_id
    )
);

// WRONG - Never do this
$results = $wpdb->query( "SELECT * FROM {$wpdb->prefix}frm_items WHERE form_id = " . $form_id );
```

### Formidable Database Patterns

```php
// Use FrmDb methods when available
FrmDb::get_col( $table, $where, $field );
FrmDb::get_var( $table, $where, $field );
FrmDb::get_results( $table, $where, $fields, $args );
```

## Sanitization Functions

Use the correct function for each data type:

| Data Type | Function                        |
| --------- | ------------------------------- |
| Text      | `sanitize_text_field()`         |
| Textarea  | `sanitize_textarea_field()`     |
| Email     | `sanitize_email()`              |
| URL       | `esc_url_raw()`                 |
| Integer   | `absint()` or `intval()`        |
| Filename  | `sanitize_file_name()`          |
| HTML      | `wp_kses()` or `wp_kses_post()` |
| Key       | `sanitize_key()`                |

## Escaping Functions

Escape based on output context:

| Context    | Function           |
| ---------- | ------------------ |
| HTML       | `esc_html()`       |
| Attribute  | `esc_attr()`       |
| URL        | `esc_url()`        |
| JavaScript | `esc_js()`         |
| Textarea   | `esc_textarea()`   |
| SQL        | `$wpdb->prepare()` |

## Formidable Helper Methods

```php
// Use FrmAppHelper for common operations
FrmAppHelper::get_param( $param, $default, $src, $sanitize );
FrmAppHelper::get_post_param( $param, $default, $sanitize );
FrmAppHelper::simple_get( $param, $sanitize, $default );
FrmAppHelper::kses( $value, $allowed );
FrmAppHelper::sanitize_with_html( $value );
```

## Hooks and Filters

### Naming Convention

```php
// Lite hooks
do_action( 'frm_action_name', $args );
apply_filters( 'frm_filter_name', $value, $args );

// Pro hooks
do_action( 'frm_pro_action_name', $args );
apply_filters( 'frm_pro_filter_name', $value, $args );
```

### Hook Documentation

```php
/**
 * Fires after form submission.
 *
 * @since 2.0
 *
 * @param int   $entry_id Entry ID.
 * @param int   $form_id  Form ID.
 * @param array $values   Submitted values.
 */
do_action( 'frm_after_create_entry', $entry_id, $form_id, $values );
```

## AJAX Handlers

```php
// Always verify nonce and capabilities
public static function ajax_handler() {
    check_ajax_referer( 'frm_ajax', 'nonce' );

    if ( ! current_user_can( 'frm_edit_forms' ) ) {
        wp_die( -1 );
    }

    // Handler code...

    wp_send_json_success( $data );
}
```

## Performance Considerations

- Cache expensive queries with transients or object cache.
- Use LIMIT in queries - never fetch unlimited results.
- Avoid queries in loops - batch operations when possible.
- Use `wp_cache_get()` / `wp_cache_set()` for repeated lookups.
- Index database columns used in WHERE clauses.

## Internationalization

```php
// Text domain for Lite
__( 'Text', 'formidable' );
esc_html__( 'Text', 'formidable' );

// Text domain for Pro
__( 'Text', 'formidable-pro' );

// With placeholders
sprintf(
    /* translators: %s: Field name */
    __( 'The %s field is required.', 'formidable' ),
    $field_name
);
```
