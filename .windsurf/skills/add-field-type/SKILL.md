---
name: add-field-type
description: Guides creation of a new Formidable field type following all patterns and standards
---

# Add New Field Type

Follow these steps to create a new field type for Formidable Forms.

## Step 1: Determine Location

- **Lite field** → `formidable-master/classes/models/fields/`
- **Pro field** → `formidable-pro-master/classes/models/fields/`

## Step 2: Create Field Class

Create new file: `FrmFieldYourType.php` (Lite) or `FrmProFieldYourType.php` (Pro)

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}

/**
 * Your Field type description.
 *
 * @since x.x
 */
class FrmProFieldYourType extends FrmFieldType {

    /**
     * {@inheritDoc}
     */
    protected $type = 'your_type';

    /**
     * {@inheritDoc}
     */
    protected $has_input = true;

    /**
     * {@inheritDoc}
     */
    protected $has_html = true;

    // Add other property overrides as needed...

    /**
     * {@inheritDoc}
     */
    protected function field_settings_for_type() {
        $settings = parent::field_settings_for_type();
        // Customize settings...
        return $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function get_new_field_defaults() {
        $defaults = parent::get_new_field_defaults();
        $defaults['name'] = __( 'Your Field', 'formidable-pro' );
        return $defaults;
    }
}
```

## Step 3: Register Field Type

Add to `FrmFieldFactory::field_type_classes()` or use filter:

```php
add_filter( 'frm_get_field_type_class', 'register_your_field_type', 10, 2 );
function register_your_field_type( $class, $type ) {
    if ( 'your_type' === $type ) {
        return 'FrmProFieldYourType';
    }
    return $class;
}
```

## Step 4: Add to Field Selection

For Pro fields, add to `FrmProField::pro_field_selection()`:

```php
'your_type' => array(
    'name' => __( 'Your Field', 'formidable-pro' ),
    'icon' => 'frmfont frm_your_icon',
),
```

## Step 5: Create Form Builder View

Create view file: `classes/views/frmpro-fields/back-end/field-yourtype.php`

## Step 6: Add Tests

Create test file: `tests/fields/test_FrmProFieldYourType.php`

```php
<?php
class WP_Test_FrmProFieldYourType extends FrmProUnitTest {

    public function test_field_creation() {
        $field = $this->factory->field->create_and_get(
            array(
                'type' => 'your_type',
                'form_id' => $this->factory->form->create(),
            )
        );
        $this->assertEquals( 'your_type', $field->type, 'Field type should be your_type.' );
    }
}
```

## Checklist

- [ ] Field class extends `FrmFieldType`
- [ ] Type registered in factory
- [ ] Added to field selection array
- [ ] Form builder view created
- [ ] Front-end rendering works
- [ ] Value saving works
- [ ] Validation works
- [ ] Tests written
- [ ] PHPDoc complete
