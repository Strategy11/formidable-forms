# Best Practices

**Priority: MEDIUM**  
**Impact: Maintainability and debugging**

---

## Self-Explanatory Flag Values

Use descriptive strings instead of boolean flags.

**Incorrect:**

```php
function eat( $what, $slowly = true ) {}

eat( 'mushrooms' );
eat( 'mushrooms', true );   // What does true mean?
eat( 'dogfood', false );    // What does false mean?
```

**Correct:**

```php
function eat( $what, $speed = 'slowly' ) {}

eat( 'mushrooms' );
eat( 'mushrooms', 'slowly' );
eat( 'dogfood', 'quickly' );
```

**For multiple options, use an array:**

```php
function eat( $what, $args = array() ) {}

eat( 'noodles', array( 'speed' => 'moderate' ) );
```

---

## Avoid Clever Code

Readability over cleverness. Use strict comparisons. No assignments in conditionals.

**Incorrect:**

```php
isset( $var ) || $var = some_function();

if ( $data = $wpdb->get_var( '...' ) ) {
    // Use $data
}

if ( 0 == strpos( 'WordPress', 'foo' ) ) {}  // Loose comparison
```

**Correct:**

```php
if ( ! isset( $var ) ) {
    $var = some_function();
}

$data = $wpdb->get_var( '...' );
if ( $data ) {
    // Use $data
}

if ( 0 === strpos( $text, 'WordPress' ) ) {}  // Strict comparison
```

**Switch fall-through must be documented:**

```php
switch ( $foo ) {
    case 'bar':
        // Empty case can fall through without comment
    case 'baz':
        echo esc_html( $foo );
        break;

    case 'dog':
        echo 'horse';
        // no break - explicit comment required
    case 'fish':
        echo 'bird';
        break;
}
```

**Never use:**

- `goto` statement
- `eval()` construct
- `create_function()` (deprecated/removed)

---

## Closures

Closures are allowed but should NOT be used as filter/action callbacks (difficult to remove).

**Acceptable:**

```php
$caption = preg_replace_callback(
    '/<[a-zA-Z0-9]+(?: [^<>]+>)*/',
    function ( $matches ) {
        return preg_replace( '/[\r\n\t]+/', ' ', $matches[0] );
    },
    $caption
);
```

**Not recommended for hooks:**

```php
// Hard to remove with remove_action()
add_action( 'init', function() {
    // ...
} );
```

---

## Don't Use extract()

Never use `extract()`. It makes code harder to debug and understand.

**Incorrect:**

```php
extract( $args );
echo $title;  // Where did $title come from?
```

**Correct:**

```php
$title = $args['title'];
echo $title;
```

---

## Regular Expressions

Use PCRE (`preg_` functions). Never use `/e` modifier. Use single-quoted strings.

**Incorrect:**

```php
preg_replace( '/pattern/e', 'replacement', $subject );  // /e is deprecated
```

**Correct:**

```php
preg_replace_callback(
    '/pattern/',
    function ( $matches ) {
        return process( $matches[0] );
    },
    $subject
);
```
