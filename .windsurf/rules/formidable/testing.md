---
trigger: model_decision
description: Formidable Forms testing conventions and requirements. Apply when writing or modifying tests.
---

# Formidable Forms Testing

Testing conventions specific to Formidable Forms plugin.

---

## Required Test Scenarios

Every fix/feature **MUST** be tested with these scenarios before completion:

### Plugin State Scenarios

1. **Pro active**: Test with formidable-pro plugin enabled
2. **Pro inactive**: Test in Lite-only mode
3. **Fresh install**: No prior data exists
4. **Migration scenario**: Upgrading from previous version

### Data Scenarios

1. **Empty data**: Empty arrays, null values, missing keys
2. **Valid data**: Expected input with correct types
3. **Invalid data**: Wrong types, malformed input
4. **Edge cases**: Boundary values, special characters, Unicode
5. **Large datasets**: Performance with many entries/fields

### User Scenarios

1. **Administrator**: Full capabilities
2. **Editor**: Limited capabilities
3. **Subscriber**: Minimal capabilities
4. **Logged out**: No user context

---

## Test Classes

- **Lite tests:** Extend `FrmUnitTest`
- **Pro tests:** Extend `FrmProUnitTest`
- **AJAX tests:** Extend `FrmAjaxUnitTest`

```php
class Test_FrmField extends FrmUnitTest {

    public function test_method_does_expected_behavior() {
        // Arrange
        $form = $this->factory->form->create_and_get();

        // Act
        $result = FrmField::get_all_for_form( $form->id );

        // Assert
        $this->assertIsArray( $result, 'Result should be an array.' );
    }
}
```

---

## Test Method Naming

Use descriptive names that explain the scenario:

```php
// Pattern: test_{method}_{scenario}_{expected_outcome}
public function test_get_field_returns_null_for_invalid_id() {}
public function test_create_entry_saves_meta_values() {}
public function test_delete_form_removes_all_fields() {}
public function test_validate_field_rejects_empty_required_field() {}
```

---

## Test Structure (AAA Pattern)

Follow **Arrange-Act-Assert** pattern:

```php
public function test_entry_creation_with_valid_data() {
    // Arrange - Set up test data and conditions
    $form  = $this->factory->form->create_and_get();
    $field = $this->factory->field->create_and_get( array(
        'form_id' => $form->id,
        'type'    => 'text',
    ) );

    // Act - Execute the code being tested
    $entry_id = FrmEntry::create( array(
        'form_id' => $form->id,
        'item_meta' => array(
            $field->id => 'Test Value',
        ),
    ) );

    // Assert - Verify the results
    $this->assertIsNumeric( $entry_id, 'Entry ID should be numeric.' );
    $this->assertGreaterThan( 0, $entry_id, 'Entry ID should be positive.' );
}
```

---

## Factory Methods

Use factory methods for test data creation:

```php
// Create form
$form = $this->factory->form->create_and_get();

// Create field with options
$field = $this->factory->field->create_and_get( array(
    'form_id' => $form->id,
    'type'    => 'text',
    'name'    => 'Test Field',
) );

// Create entry
$entry = $this->factory->entry->create_and_get( array(
    'form_id' => $form->id,
) );

// Create multiple items
$entries = $this->factory->entry->create_many( 5, array(
    'form_id' => $form->id,
) );

// Create user with role
$user_id = $this->factory->user->create( array(
    'role' => 'editor',
) );
```

---

## Testing Private/Protected Methods

```php
// For private methods
$result = $this->run_private_method(
    array( $object, 'private_method_name' ),
    array( $arg1, $arg2 )
);

// For private properties
$value = $this->get_private_property( $object, 'property_name' );
$this->set_private_property( $object, 'property_name', $new_value );
```

---

## User Context Testing

```php
public function test_admin_can_delete_form() {
    // Set user role
    $this->set_user_by_role( 'administrator' );

    $form = $this->factory->form->create_and_get();
    $result = FrmForm::destroy( $form->id );

    $this->assertTrue( $result, 'Admin should be able to delete form.' );
}

public function test_subscriber_cannot_delete_form() {
    $this->set_user_by_role( 'subscriber' );

    $form = $this->factory->form->create_and_get();
    $result = FrmForm::destroy( $form->id );

    $this->assertFalse( $result, 'Subscriber should not delete form.' );
}
```

---

## Assertion Best Practices

### Use Specific Assertions

```php
// CORRECT - Specific assertions
$this->assertSame( $expected, $actual, 'Values should be identical.' );
$this->assertIsArray( $result, 'Result should be an array.' );
$this->assertArrayHasKey( 'id', $data, 'Data should have id key.' );
$this->assertStringContainsString( 'error', $message, 'Message should contain error.' );

// INCORRECT - Generic assertions
$this->assertTrue( $expected === $actual );
$this->assertTrue( is_array( $result ) );
```

### Message Requirements

All assertion messages must end with a period:

```php
$this->assertEquals( $expected, $actual, 'The value should match expected.' );
$this->assertTrue( $condition, 'Condition should be true.' );
$this->assertNotEmpty( $data, 'Data should not be empty.' );
```

---

## Data Providers

Use data providers for testing multiple scenarios:

```php
/**
 * @dataProvider field_type_provider
 */
public function test_field_validation( $field_type, $value, $expected_valid ) {
    $field = $this->factory->field->create_and_get( array(
        'type' => $field_type,
    ) );

    $is_valid = FrmEntryValidate::validate_field( $field, $value );

    $this->assertSame( $expected_valid, $is_valid );
}

public function field_type_provider() {
    return array(
        'text with value'     => array( 'text', 'hello', true ),
        'text empty'          => array( 'text', '', true ),
        'email valid'         => array( 'email', 'test@example.com', true ),
        'email invalid'       => array( 'email', 'invalid', false ),
        'number valid'        => array( 'number', '123', true ),
        'number with letters' => array( 'number', 'abc', false ),
    );
}
```

---

## Mocking and Stubbing

```php
public function test_api_request_handles_error() {
    // Mock HTTP response
    add_filter( 'pre_http_request', function() {
        return new WP_Error( 'http_error', 'Connection failed' );
    } );

    $result = FrmAPI::make_request( '/endpoint' );

    $this->assertInstanceOf( WP_Error::class, $result );
}
```

---

## Front-end vs Admin Testing

```php
public function test_shortcode_output_on_frontend() {
    // Switch to front-end context
    $this->set_front_end();

    $output = do_shortcode( '[formidable id="1"]' );

    $this->assertStringContainsString( '<form', $output );
}

public function test_admin_page_loads() {
    // Switch to admin context
    $this->set_admin_screen( 'admin.php?page=formidable' );

    $this->assertTrue( is_admin(), 'Should be in admin context.' );
}
```

---

## Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific test file
vendor/bin/phpunit tests/phpunit/fields/test-FrmField.php

# Specific test method
vendor/bin/phpunit --filter test_method_name

# With coverage
vendor/bin/phpunit --coverage-html coverage/

# Specific group
vendor/bin/phpunit --group ajax
```

---

## Test File Organization

```text
tests/phpunit/
├── base/
│   ├── FrmUnitTest.php
│   ├── FrmAjaxUnitTest.php
│   └── testdata.xml
├── fields/
│   ├── test-FrmField.php
│   └── test-FrmFieldValidation.php
├── forms/
│   ├── test-FrmForm.php
│   └── test-FrmFormAction.php
├── entries/
│   └── test-FrmEntry.php
└── helpers/
    └── test-FrmAppHelper.php
```

---

## Code Style Check

```bash
# PHPCS check
vendor/bin/phpcs --standard=phpcs.xml path/to/file.php

# Auto-fix
vendor/bin/phpcbf --standard=phpcs.xml path/to/file.php
```
