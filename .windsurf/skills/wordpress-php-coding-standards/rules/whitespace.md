# Whitespace

**Priority: MEDIUM**  
**Impact: Readability and visual consistency**

---

## Space Usage

Spaces after commas and around operators. Spaces inside parentheses of control structures.

**Operators:**

```php
SOME_CONST === 23;
foo() && bar();
! $foo;
array( 1, 2, 3 );
$baz . '-5';
$term .= 'X';
$result = 2 ** 3;
```

**Control structures:**

```php
foreach ( $foo as $bar ) {
    // ...
}

if ( $foo && ( $bar || $baz ) ) {
    // ...
}
```

**Functions:**

```php
function my_function( $param1 = 'foo', $param2 = 'bar' ) {
    // ...
}

my_function( $param1, func_param( $param2 ) );
```

**Array access (space only around variables):**

```php
$x = $foo['bar'];     // Correct - no space for literal
$x = $foo[0];         // Correct - no space for number
$x = $foo[ $bar ];    // Correct - space for variable
```

**Type casts (lowercase, short form):**

```php
$foo = (bool) $bar;   // Correct
$foo = (int) $value;  // Correct

$foo = (boolean) $bar; // Incorrect - use (bool)
$foo = (integer) $value; // Incorrect - use (int)
```

**Increment/decrement (no space):**

```php
for ( $i = 0; $i < 10; $i++ ) {}
++$b;
```

---

## Indentation

Use real tabs, not spaces. Spaces may be used mid-line for alignment.

**Correct:**

```php
$foo   = 'somevalue';
$foo2  = 'somevalue2';
$foo34 = 'somevalue3';
```

**Associative arrays (one item per line when more than one):**

```php
// Single item - can be one line
$query = new WP_Query( array( 'ID' => 123 ) );

// Multiple items - each on own line
$args = array(
    'post_type'   => 'page',
    'post_author' => 123,
    'post_status' => 'publish',
);
```

**Switch statements:**

```php
switch ( $type ) {
    case 'foo':
        some_function();
        break;

    case 'bar':
        some_function();
        break;
}
```

---

## Trailing Spaces

Remove trailing whitespace at end of lines. Omit closing PHP tag at end of file (preferred). No trailing blank lines at end of function body.
