---
trigger: model_decision
description: Formidable Forms testing conventions and requirements. Apply when writing or modifying tests.
---

# Formidable Forms Testing

Testing conventions specific to Formidable Forms plugin.

---

## Test Classes

- **Lite tests:** Extend `FrmUnitTest`
- **Pro tests:** Extend `FrmProUnitTest`

```php
class Test_FrmField extends FrmUnitTest {
    // Tests
}
```

---

## Test Data Creation

Use factory methods for test data.

```php
$form = $this->factory->form->create_and_get();
$field = $this->factory->field->create_and_get( array(
    'form_id' => $form->id,
    'type'    => 'text',
) );
$entry = $this->factory->entry->create_and_get( array(
    'form_id' => $form->id,
) );
```

---

## Testing Protected Methods

```php
$result = $this->run_private_method( array( $object, 'method_name' ), array( $arg1, $arg2 ) );
```

---

## Assertion Messages

All assertion messages must end with a period.

```php
$this->assertEquals( $expected, $actual, 'The value should match expected.' );
$this->assertTrue( $condition, 'Condition should be true.' );
```

---

## Required Test Scenarios

Every fix/feature must be tested with:

1. **Pro active**: With formidable-pro plugin enabled
2. **Pro inactive**: Lite-only scenario
3. **Empty data**: Empty arrays, null values, missing keys
4. **Edge cases**: Boundary conditions, special characters

---

## Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific test file
vendor/bin/phpunit tests/phpunit/test-file.php

# Specific test method
vendor/bin/phpunit --filter test_method_name
```

---

## Code Style Check

```bash
vendor/bin/phpcs --standard=phpcs.xml path/to/file.php
```
