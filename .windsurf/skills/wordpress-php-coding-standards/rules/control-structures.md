# Control Structures

**Priority: MEDIUM**  
**Impact: Bug prevention and syntax compatibility**

---

## Yoda Conditions

Put constants/literals on the left side of comparisons.

**Incorrect:**

```php
if ( $the_force == true ) {
    // Accidental assignment possible: if ( $the_force = true )
}
```

**Correct:**

```php
if ( true === $the_force ) {
    $victorious = you_will( $be );
}
```

**Applies to:** `==`, `!=`, `===`, `!==`

**Does not apply to:** `<`, `>`, `<=`, `>=` (too difficult to read)

---

## Use elseif

Use `elseif`, not `else if`. Required for alternative syntax compatibility.

**Incorrect:**

```php
if ( condition ) {
    // ...
} else if ( condition2 ) {
    // ...
}
```

**Correct:**

```php
if ( condition ) {
    // ...
} elseif ( condition2 ) {
    // ...
}
```
