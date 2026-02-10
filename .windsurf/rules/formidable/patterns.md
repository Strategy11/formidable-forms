---
trigger: always_on
description: Formidable Forms plugin-specific patterns and conventions. Always active for this workspace.
---

# Formidable Forms Patterns

Formidable-specific patterns, naming conventions, and architectural decisions.

---

## Pattern Discovery Process

Before making ANY change:

1. **Find existing patterns** — Search models, controllers, helpers for similar functionality
2. **Study pattern usage** — Search ALL places using the pattern
3. **Trace parent hierarchy** — Search parent files up to plugin root
4. **Iterate if needed** — If better pattern found, repeat from step 1

**Never invent custom solutions if existing patterns exist.**

---

## Class Naming

- **Lite plugin:** `FrmClassName` (e.g., `FrmAppHelper`, `FrmDb`, `FrmField`)
- **Pro plugin:** `FrmProClassName` (e.g., `FrmProAppHelper`, `FrmProField`)

---

## Method Naming

- **Public methods:** `snake_case`
- **Legacy methods:** Preserve existing `camelCase` for backward compatibility

---

## Hook Naming

- **Lite hooks:** `frm_hook_name`
- **Pro hooks:** `frm_pro_hook_name`

---

## Text Domains

- **Lite:** `formidable`
- **Pro and add-ons:** `formidable-pro`

---

## Factory Pattern

Use `FrmFieldFactory::get_field_type()` for field type instances.

```php
$field_obj = FrmFieldFactory::get_field_type( $field );
```

---

## Helper Classes

### FrmAppHelper

Common utility methods for the plugin.

```php
FrmAppHelper::get_post_param( 'key', 'default', 'sanitize_text_field' );
FrmAppHelper::get_param( 'key', 'default', 'get', 'sanitize_text_field' );
FrmAppHelper::simple_get( 'key', 'sanitize_text_field' );
```

### FrmDb

Database operations wrapper.

```php
FrmDb::get_col( $table, $where, $field );
FrmDb::get_row( $table, $where, $fields );
FrmDb::get_results( $table, $where, $fields );
FrmDb::get_var( $table, $where, $field );
```

### FrmFieldsHelper

Field-specific utilities.

---

## Pro Plugin Awareness

**All code must work when:**

- Pro is active
- Pro is inactive

```php
// Check if Pro is active
if ( class_exists( 'FrmProAppHelper' ) ) {
    // Pro-specific code
}

// Or use the constant
if ( defined( 'FRM_PRO_VERSION' ) ) {
    // Pro-specific code
}
```
