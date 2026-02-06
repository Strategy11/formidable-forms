# Operators

**Priority: MEDIUM**  
**Impact: Readability and error handling**

---

## Ternary Operator

Test for true, not false (except with `! empty()`). Don't use short ternary.

**Incorrect:**

```php
$value = $condition ?: 'default';  // Short ternary - not allowed
$type = ( ! $is_valid ) ? 'invalid' : 'valid';  // Testing for false
```

**Correct:**

```php
$musictype = ( 'jazz' === $music ) ? 'cool' : 'blah';
$value = ( ! empty( $field ) ) ? $field : 'default';
```

---

## Increment/Decrement Operators

Prefer pre-increment/decrement for standalone statements.

**Incorrect:**

```php
$a--;
$count++;
```

**Correct:**

```php
--$a;
++$count;
```

---

## Error Control Operator

Don't use `@` to suppress errors. Do proper error checking instead.

**Incorrect:**

```php
$value = @file_get_contents( $file );
```

**Correct:**

```php
if ( file_exists( $file ) && is_readable( $file ) ) {
    $value = file_get_contents( $file );
}
```
