---
trigger: glob
globs: ["**/*.php"]
description: WordPress PHP coding standards. Auto-applies when working with PHP files.
---

# WordPress PHP Coding Standards

Based on WordPress Core Official Standards. Apply when maintaining, generating, or refactoring PHP code.

**Reference:** [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)

---

## PHP Version Requirement

**Target Version: PHP 7.0**

All new code must be compatible with PHP 7.0. Use modern PHP 7.0 syntax but do not use features from PHP 7.1 or higher.

### PHP 7.0 Features (Use These)

- Scalar type declarations: `function foo(int $id, string $name)`
- Return type declarations: `function foo(): bool`
- Null coalescing operator: `$value = $array['key'] ?? 'default'`
- Spaceship operator: `$result = $a <=> $b`
- Anonymous classes: `new class { ... }`
- Group use declarations: `use Some\Namespace\{ClassA, ClassB}`
- `define()` with arrays: `define('ITEMS', ['a', 'b'])`

### PHP 7.1+ Features (Do NOT Use)

- Nullable types: `?string` (PHP 7.1)
- Void return type: `: void` (PHP 7.1)
- Class constant visibility: `private const` (PHP 7.1)
- Iterable pseudo-type: `iterable` (PHP 7.1)
- Multi-catch exceptions: `catch (A | B $e)` (PHP 7.1)
- Negative string offsets: `$str[-1]` (PHP 7.1)
- Object type hint: `object` (PHP 7.2)
- Trailing commas in function calls (PHP 7.3)
- Arrow functions: `fn($x) => $x * 2` (PHP 7.4)
- Typed properties: `public int $id` (PHP 7.4)
- Null safe operator: `?->` (PHP 8.0)
- Named arguments (PHP 8.0)
- Match expression (PHP 8.0)
- Constructor property promotion (PHP 8.0)

### Example PHP 7.0 Compatible Code

```php
/**
 * Process user data.
 *
 * @param int    $user_id User ID.
 * @param string $action  Action to perform.
 * @param array  $options Optional settings.
 * @return bool Whether the operation succeeded.
 */
function process_user_data( int $user_id, string $action, array $options = array() ): bool {
	$timeout = $options['timeout'] ?? 30;
	$result  = $options['result'] ?? 'default';

	if ( empty( $user_id ) ) {
		return false;
	}

	return true;
}
```

---

## 1. Security and Database

### Prepared Statements

Always use `$wpdb->prepare()` for queries with variables.

```php
$wpdb->query(
	$wpdb->prepare(
		"UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d",
		$var,
		$id
	)
);
```

### Placeholders

| Placeholder | Type                           |
| ----------- | ------------------------------ |
| `%d`        | Integer (whole number)         |
| `%f`        | Float (decimal number)         |
| `%s`        | String                         |
| `%i`        | Identifier (table/field names) |

### SQL Formatting

- Capitalize SQL keywords (SELECT, FROM, WHERE, etc.)
- Break complex statements into multiple lines
- Use consistent indentation for readability

```php
$wpdb->query(
	$wpdb->prepare(
		"
		SELECT post_title, post_content
		FROM $wpdb->posts
		WHERE post_status = %s
			AND post_type = %s
		ORDER BY post_date DESC
		LIMIT %d
		",
		'publish',
		'post',
		10
	)
);
```

---

## 2. Naming Conventions

### Variables and Functions

Use lowercase with underscores. Never use camelCase.

```php
function some_function( $some_variable ) {
	$local_variable = '';
}
```

### Classes, Interfaces, Traits, Enums

Capitalized words separated by underscores. Acronyms should be uppercase.

```php
class Walker_Category extends Walker {}
class WP_HTTP {}
interface Arrayable {}
trait Singleton {}
enum Post_Status {}
```

### Constants

All uppercase with underscores.

```php
define( 'DOING_AJAX', true );
const MY_CONSTANT = 'value';
```

### Dynamic Hooks

Use interpolation, not concatenation.

```php
// Correct
do_action( "{$new_status}_{$post->post_type}", $post->ID, $post );

// Incorrect
do_action( $new_status . '_' . $post->post_type, $post->ID, $post );
```

---

## 3. Formatting

### Brace Style

Always use braces even for single-line blocks. Opening brace on same line.

```php
if ( condition ) {
	action();
} elseif ( condition2 ) {
	action2();
} else {
	default_action();
}
```

### Array Syntax

Use long array syntax `array()`, not short syntax `[]`.

```php
$args = array(
	'post_type'   => 'page',
	'post_status' => 'publish',
);
```

### Multi-line Function Calls

```php
$result = some_function(
	$arg1,
	$arg2,
	array(
		'key1' => 'value1',
		'key2' => 'value2',
	)
);
```

### Type Declarations

One space before and after type declarations.

```php
function foo( Class_Name $parameter, callable $callable, int $count = 0 ): bool {
	return true;
}
```

### Spread Operator

No space between spread operator and variable.

```php
function foo( &...$spread ) {
	bar( ...$spread );
}
```

---

## 4. Whitespace and Indentation

### Space Usage

| Context                              | Rule                           |
| ------------------------------------ | ------------------------------ |
| After commas                         | Space required                 |
| Around operators                     | Spaces required                |
| Inside control structure parentheses | Spaces required                |
| Array literal access                 | No space: `$foo['bar']`        |
| Array variable access                | Space required: `$foo[ $bar ]` |
| Function call parentheses            | No space inside                |
| Type casts                           | Space after: `(int) $value`    |

```php
// Correct examples
$x = ( $a + $b ) * $c;
if ( true === $condition ) {}
$value = $array['literal'];
$value = $array[ $variable ];
some_function( $arg1, $arg2 );
$integer = (int) $value;
```

### Indentation

Use real tabs for indentation, not spaces.

### Blank Lines

- One blank line after opening PHP tag
- One blank line before closing PHP tag (if used)
- No extra blank lines at start/end of function body

---

## 5. Control Structures

### Yoda Conditions

Put the constant or literal value on the left side of comparisons.

```php
// Correct
if ( true === $the_force ) {
	$victorious = you_will( $be );
}

// Incorrect
if ( $the_force === true ) {
	$victorious = you_will( $be );
}
```

### elseif vs else if

Always use `elseif`, not `else if`.

```php
// Correct
if ( condition ) {
	action();
} elseif ( condition2 ) {
	action2();
}

// Incorrect
if ( condition ) {
	action();
} else if ( condition2 ) {
	action2();
}
```

### Alternative Syntax in Templates

Use alternative syntax with explicit ending statements in templates.

```php
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
<?php endif; ?>
```

---

## 6. Operators

### Ternary Operator

- Test for true, not false
- Do not use short ternary `?:`
- Ternaries should not be nested

```php
// Correct
$value = ( ! empty( $field ) ) ? $field : 'default';

// Incorrect
$value = empty( $field ) ? 'default' : $field;

// Incorrect - short ternary
$value = $field ?: 'default';
```

### Null Coalescing Operator

Use `??` for null checks.

```php
$value = $array['key'] ?? 'default';
```

### Error Control Operator

Never use `@` to suppress errors.

```php
// Incorrect
$value = @file_get_contents( $file );

// Correct
if ( file_exists( $file ) && is_readable( $file ) ) {
	$value = file_get_contents( $file );
}
```

### Increment/Decrement

Do not use pre-increment/decrement in standalone statements.

```php
// Correct
$a++;
++$b;

// Do not use results in expressions
$a = $b++;
```

---

## 7. Best Practices

### Boolean Arguments

Use descriptive string values instead of boolean flags.

```php
// Correct
function some_function( $args ) {
	$type = $args['type'] ?? 'default';
}
some_function( array( 'type' => 'hierarchical' ) );

// Incorrect
function some_function( $hierarchical = false ) {}
some_function( true );
```

### Readability Over Cleverness

Write clear and readable code. Avoid clever one-liners that are hard to understand.

### Assignments in Conditionals

Never put assignments inside conditionals.

```php
// Incorrect
if ( $result = some_function() ) {}

// Correct
$result = some_function();
if ( $result ) {}
```

### Strict Comparisons

Always use strict comparisons (`===` and `!==`) unless loose comparison is explicitly required.

### Closures as Callbacks

Do not use closures (anonymous functions) as callbacks for actions and filters.

```php
// Incorrect
add_action( 'init', function() {
	// code
} );

// Correct
add_action( 'init', 'my_init_function' );
function my_init_function() {
	// code
}
```

### Forbidden Functions

| Function            | Reason                   |
| ------------------- | ------------------------ |
| `extract()`         | Makes code unpredictable |
| `eval()`            | Security vulnerability   |
| `create_function()` | Deprecated and insecure  |
| `compact()`         | Reduces readability      |

### Regular Expressions

Use PCRE functions (`preg_match`, `preg_replace`, etc.) instead of POSIX functions.

---

## 8. General Syntax

### PHP Tags

Always use full PHP tags. Never use shorthand.

```php
// Correct
<?php
echo 'Hello';
?>

// Incorrect
<?
echo 'Hello';
?>

// Incorrect
<?= 'Hello' ?>
```

### Quotes

Use single quotes when not evaluating anything inside the string.

```php
// Correct
$str = 'Hello World';
$str = "Hello $name";
$str = "Hello {$user->name}";

// Incorrect
$str = "Hello World";
```

### String Concatenation

Space on both sides of the concatenation operator.

```php
$string = 'Hello ' . $name . ', welcome!';
```

### Include and Require

Do not use parentheses. Use `require_once` for dependencies.

```php
// Correct
require_once ABSPATH . 'wp-admin/includes/file.php';

// Incorrect
require_once( ABSPATH . 'wp-admin/includes/file.php' );
```

---

## 9. Object-Oriented Programming

### One Structure Per File

Each class, interface, trait, or enum should be in its own file.

### Visibility

Always declare visibility for properties and methods.

```php
class My_Class {
	public $public_property;
	protected $protected_property;
	private $private_property;

	public function public_method() {}
	protected function protected_method() {}
	private function private_method() {}
}
```

### Class Instantiation

Always use parentheses when instantiating a class.

```php
// Correct
$instance = new My_Class();

// Incorrect
$instance = new My_Class;
```

### Object Operator Spacing

No spaces around the object operator.

```php
$object->property;
$object->method();
```

---

## 10. Namespaces and Imports

### Namespace Naming

Capitalized words separated by underscores.

```php
namespace Jenga\Starter_Plugin;
namespace Jenga\Starter_Plugin\Helpers;
```

### Namespace Declaration

One namespace per file, at the top after the PHP opening tag.

### WordPress Prefix

Never use `WP_` or `WordPress` as a namespace prefix.

### Use Statements

One use statement per line.

```php
use Jenga\Starter_Plugin\Helpers\Array_Helper;
use Jenga\Starter_Plugin\Helpers\String_Helper;
```

---

## 11. Shell Commands

### No Backticks

Never use backtick operator for shell commands.

```php
// Incorrect
$output = `ls -la`;

// Correct
$output = shell_exec( 'ls -la' );
```

### Escape User Input

Always escape user input before using in shell commands.

```php
$safe_arg = escapeshellarg( $user_input );
$safe_cmd = escapeshellcmd( $command );
$output = shell_exec( "ls $safe_arg" );
```

---

## 12. Magic Methods

Use magic methods appropriately and document them.

```php
/**
 * Get a property value.
 *
 * @param string $name Property name.
 * @return mixed Property value.
 */
public function __get( $name ) {
	return $this->data[ $name ] ?? null;
}
```

---

## 13. Closures

### Spacing

Space after `function` keyword. Space before and after `use`.

```php
$closure = function ( $arg ) use ( $var ) {
	return $arg . $var;
};
```

### Arrow Functions

Space around the arrow.

```php
$double = fn( $n ) => $n * 2;
```

---

## Tooling

```bash
# Install WordPress Coding Standards
composer require --dev wp-coding-standards/wpcs

# Check a file
./vendor/bin/phpcs --standard=WordPress path/to/file.php

# Auto-fix issues
./vendor/bin/phpcbf --standard=WordPress path/to/file.php
```
