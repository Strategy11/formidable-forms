# WordPress PHP Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring PHP code in the WordPress ecosystem.

---

## Abstract

These PHP coding standards are the official WordPress coding standards for PHP. They are mandatory for WordPress Core and recommended for all plugins and themes. They encompass not just code style, but also best practices for interoperability, translatability, and security.

---

## Table of Contents

1. [Security & Database](#1-security--database) — **CRITICAL**
   - 1.1 [Database Queries](#11-database-queries)
   - 1.2 [SQL Formatting](#12-sql-formatting)
2. [Naming Conventions](#2-naming-conventions) — **HIGH**
   - 2.1 [Variables and Functions](#21-variables-and-functions)
   - 2.2 [Classes, Interfaces, Traits, Enums](#22-classes-interfaces-traits-enums)
   - 2.3 [Constants](#23-constants)
   - 2.4 [File Naming](#24-file-naming)
   - 2.5 [Dynamic Hooks](#25-dynamic-hooks)
3. [Formatting](#3-formatting) — **HIGH**
   - 3.1 [Brace Style](#31-brace-style)
   - 3.2 [Array Syntax](#32-array-syntax)
   - 3.3 [Multiline Function Calls](#33-multiline-function-calls)
   - 3.4 [Type Declarations](#34-type-declarations)
4. [Whitespace](#4-whitespace) — **MEDIUM**
   - 4.1 [Space Usage](#41-space-usage)
   - 4.2 [Indentation](#42-indentation)
   - 4.3 [Trailing Spaces](#43-trailing-spaces)
5. [Control Structures](#5-control-structures) — **MEDIUM**
   - 5.1 [Yoda Conditions](#51-yoda-conditions)
   - 5.2 [Use elseif](#52-use-elseif)
6. [Operators](#6-operators) — **MEDIUM**
   - 6.1 [Ternary Operator](#61-ternary-operator)
   - 6.2 [Increment/Decrement Operators](#62-incrementdecrement-operators)
   - 6.3 [Error Control Operator](#63-error-control-operator)
7. [Best Practices](#7-best-practices) — **MEDIUM**
   - 7.1 [Self-Explanatory Flag Values](#71-self-explanatory-flag-values)
   - 7.2 [Avoid Clever Code](#72-avoid-clever-code)
   - 7.3 [Closures](#73-closures)
   - 7.4 [Don't Use extract()](#74-dont-use-extract)
   - 7.5 [Regular Expressions](#75-regular-expressions)
8. [General Syntax](#8-general-syntax) — **LOW**
   - 8.1 [PHP Tags](#81-php-tags)
   - 8.2 [Quotes](#82-quotes)
   - 8.3 [Require/Include](#83-requireinclude)
9. [Namespaces & Imports](#9-namespaces--imports) — **MEDIUM**
   - 9.1 [Namespace Declarations](#91-namespace-declarations)
   - 9.2 [Import Use Statements](#92-import-use-statements)
   - 9.3 [Trait Use Statements](#93-trait-use-statements)
10. [Shell Commands](#10-shell-commands) — **HIGH**

---

## 1. Security & Database

**Impact: CRITICAL**

Database interactions must be secure and properly escaped to prevent SQL injection.

### 1.1 Database Queries

**Impact: CRITICAL (prevents SQL injection)**

Avoid touching the database directly. Use WordPress functions when available. If you must write queries, always use `$wpdb->prepare()`.

**Incorrect (direct query with unescaped data):**

```php
$wpdb->query( "UPDATE $wpdb->posts SET post_title = '$var' WHERE ID = $id" );
```

**Correct (using $wpdb->prepare()):**

```php
$var = "dangerous'";
$id = some_foo_number();

$wpdb->query(
    $wpdb->prepare(
        "UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d",
        $var,
        $id
    )
);
```

**Placeholders:**

- `%d` — integer
- `%f` — float
- `%s` — string
- `%i` — identifier (table/field names)

**Important:** Do not quote placeholders! `$wpdb->prepare()` handles escaping and quoting.

### 1.2 SQL Formatting

**Impact: MEDIUM (readability)**

Capitalize SQL keywords. Break complex statements into multiple lines.

**Correct:**

```php
$wpdb->query(
    $wpdb->prepare(
        "SELECT ID, post_title
        FROM $wpdb->posts
        WHERE post_status = %s
        AND post_type = %s
        ORDER BY post_date DESC",
        'publish',
        'post'
    )
);
```

---

## 2. Naming Conventions

**Impact: HIGH**

Consistent naming ensures code readability and discoverability.

### 2.1 Variables and Functions

**Impact: HIGH (code consistency)**

Use lowercase letters with underscores. Never use camelCase. Don't abbreviate unnecessarily.

**Incorrect:**

```php
function someFunction( $someVariable ) {}
function getData( $usrId ) {}
```

**Correct:**

```php
function some_function( $some_variable ) {}
function get_data( $user_id ) {}
```

### 2.2 Classes, Interfaces, Traits, Enums

**Impact: HIGH (OOP consistency)**

Use capitalized words separated by underscores. Acronyms should be all uppercase.

**Incorrect:**

```php
class walkerCategory extends Walker {}
class WpHttp {}
```

**Correct:**

```php
class Walker_Category extends Walker {}
class WP_HTTP {}
interface Mailer_Interface {}
trait Forbid_Dynamic_Properties {}
enum Post_Status {}
```

### 2.3 Constants

**Impact: MEDIUM (consistency)**

All uppercase with underscores separating words.

**Correct:**

```php
define( 'DOING_AJAX', true );
const MAX_UPLOAD_SIZE = 1048576;
```

### 2.4 File Naming

**Impact: MEDIUM (project organization)**

Use lowercase letters with hyphens separating words.

**General files:**

```
my-plugin-name.php
template-parts.php
```

**Class files (prefix with `class-`):**

```
class-wp-error.php       // For WP_Error class
class-walker-category.php // For Walker_Category class
```

**Template tags (suffix with `-template`):**

```
general-template.php
```

### 2.5 Dynamic Hooks

**Impact: HIGH (hook discoverability)**

Use interpolation with curly braces, not concatenation. Wrap in double quotes.

**Incorrect:**

```php
do_action( $new_status . '_' . $post->post_type, $post->ID, $post );
```

**Correct:**

```php
do_action( "{$new_status}_{$post->post_type}", $post->ID, $post );
```

Use descriptive variable names in hooks:

**Incorrect:**

```php
do_action( "save_post_{$this->id}", $data );
```

**Correct:**

```php
do_action( "save_post_{$post_id}", $data );
```

---

## 3. Formatting

**Impact: HIGH**

Structural formatting for readable and maintainable code.

### 3.1 Brace Style

**Impact: HIGH (code structure)**

Always use braces, even for single statements. Opening brace on same line.

**Incorrect:**

```php
if ( condition )
    action();

if ( condition ) action();
```

**Correct:**

```php
if ( condition ) {
    action1();
    action2();
} elseif ( condition2 && condition3 ) {
    action3();
} else {
    default_action();
}
```

**Alternative syntax for templates:**

```php
<?php if ( have_posts() ) : ?>
    <div class="hfeed">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>">
                <!-- content -->
            </article>
        <?php endwhile; ?>
    </div>
<?php endif; ?>
```

### 3.2 Array Syntax

**Impact: MEDIUM (consistency)**

Always use long array syntax `array()`, not short syntax `[]`.

**Incorrect:**

```php
$array = [ 1, 2, 3 ];
$args = [
    'post_type' => 'page',
];
```

**Correct:**

```php
$array = array( 1, 2, 3 );
$args = array(
    'post_type'   => 'page',
    'post_author' => 123,
    'post_status' => 'publish',
);
```

**Note:** Include trailing comma after the last item for cleaner diffs.

### 3.3 Multiline Function Calls

**Impact: MEDIUM (readability)**

Each parameter on its own line. Assign complex values to variables first.

**Incorrect:**

```php
$a = foo( array( 'use_this' => true, 'meta_key' => 'field_name' ), sprintf( __( 'Hello, %s!', 'textdomain' ), $friend_name ) );
```

**Correct:**

```php
$bar = array(
    'use_this' => true,
    'meta_key' => 'field_name',
);
$baz = sprintf(
    /* translators: %s: Friend's name */
    __( 'Hello, %s!', 'yourtextdomain' ),
    $friend_name
);

$a = foo(
    $bar,
    $baz,
    /* translators: %s: cat */
    sprintf( __( 'The best pet is a %s.' ), 'cat' )
);
```

### 3.4 Type Declarations

**Impact: MEDIUM (type safety)**

One space before and after type. No space between nullability operator and type.

**Incorrect:**

```php
function baz(Class_Name $param_a, String$param_b, CALLABLE $param_c ) : ? iterable {
    // Do something.
}
```

**Correct:**

```php
function foo( Class_Name $parameter, callable $callable, int $number_of_things = 0 ) {
    // Do something.
}

function bar(
    Interface_Name&Concrete_Class $param_a,
    string|int $param_b,
    callable $param_c = 'default_callable'
): User|false {
    // Do something.
}
```

---

## 4. Whitespace

**Impact: MEDIUM**

Spacing rules for visual consistency.

### 4.1 Space Usage

**Impact: MEDIUM (readability)**

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

### 4.2 Indentation

**Impact: MEDIUM (code structure)**

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

### 4.3 Trailing Spaces

**Impact: LOW (clean diffs)**

Remove trailing whitespace at end of lines. Omit closing PHP tag at end of file (preferred). No trailing blank lines at end of function body.

---

## 5. Control Structures

**Impact: MEDIUM**

Rules for conditionals and loops.

### 5.1 Yoda Conditions

**Impact: HIGH (bug prevention)**

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

### 5.2 Use elseif

**Impact: MEDIUM (syntax compatibility)**

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

---

## 6. Operators

**Impact: MEDIUM**

Proper operator usage.

### 6.1 Ternary Operator

**Impact: MEDIUM (readability)**

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

### 6.2 Increment/Decrement Operators

**Impact: LOW (performance)**

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

### 6.3 Error Control Operator

**Impact: HIGH (error handling)**

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

---

## 7. Best Practices

**Impact: MEDIUM**

Recommendations for maintainable code.

### 7.1 Self-Explanatory Flag Values

**Impact: MEDIUM (readability)**

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

### 7.2 Avoid Clever Code

**Impact: HIGH (maintainability)**

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

### 7.3 Closures

**Impact: MEDIUM (hook compatibility)**

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

### 7.4 Don't Use extract()

**Impact: HIGH (debugging)**

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

### 7.5 Regular Expressions

**Impact: MEDIUM (security/compatibility)**

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

---

## 8. General Syntax

**Impact: LOW**

Basic PHP syntax rules.

### 8.1 PHP Tags

**Impact: MEDIUM (compatibility)**

Always use full PHP tags. Never use shorthand.

**Incorrect:**

```php
<? ... ?>
<?= esc_html( $var ) ?>
```

**Correct:**

```php
<?php ... ?>
<?php echo esc_html( $var ); ?>
```

**Multiline PHP in HTML (tags on their own lines):**

```php
<?php
if ( $a === $b ) {
    ?>
    <some html>
    <?php
}
?>
```

### 8.2 Quotes

**Impact: LOW (string handling)**

Use single quotes when not evaluating variables. Alternate quote styles to avoid escaping.

**Correct:**

```php
echo '<a href="/static/link" class="button button-primary">Link name</a>';
echo "<a href='{$escaped_link}'>text with a ' single quote</a>";
```

**Always escape output in HTML attributes:**

```php
echo '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';
```

### 8.3 Require/Include

**Impact: MEDIUM (file loading)**

No parentheses. One space after keyword. Prefer `require_once` over `include_once`.

**Incorrect:**

```php
include_once( ABSPATH . 'file-name.php' );
require_once  ABSPATH . 'file-name.php';  // Extra space
```

**Correct:**

```php
require_once ABSPATH . 'file-name.php';
```

**Why `require_once`:** If file not found, `require` throws Fatal Error (stops execution). `include` only warns and continues, potentially causing security issues.

---

## Object-Oriented Programming

### One Structure Per File

Each class, interface, trait, or enum should be in its own file.

### Visibility

Always declare visibility (`public`, `protected`, `private`).

**Incorrect:**

```php
class Foo {
    var $bar;        // Old syntax
    function baz() {} // No visibility
}
```

**Correct:**

```php
class Foo {
    public $bar;

    public function baz() {}

    protected function qux() {}

    private function quux() {}
}
```

### Modifier Order

```php
final public static function foo() {}
abstract protected function bar();
```

### Object Instantiation

Always use parentheses, even without arguments.

**Incorrect:**

```php
$obj = new Foo;
```

**Correct:**

```php
$obj = new Foo();
$obj = new Foo( $arg );
```

---

## Magic Constants

Use uppercase for magic constants.

**Correct:**

```php
__DIR__
__FILE__
__CLASS__
__FUNCTION__
```

---

## Spread Operator

Space after `...` when used to spread. No space when collecting.

```php
// Spreading
function_call( ...$array );
$merged = array( ...$array1, ...$array2 );

// Collecting (variadic)
function variadic_function( ...$args ) {}
```

---

## 9. Namespaces & Imports

**Impact: MEDIUM**

Modern PHP namespace and import conventions for WordPress plugins and themes.

### 9.1 Namespace Declarations

**Impact: MEDIUM (organization)**

Each part of a namespace name should consist of capitalized words separated by underscores.

**Incorrect:**

```php
namespace prefix\admin\domainUrl\subDomain;  // camelCase not allowed
namespace Foo {
    // Code - curly brace syntax not allowed
}
namespace {
    // Global namespace declaration not allowed
}
```

**Correct:**

```php
namespace Prefix\Admin\Domain_URL\Sub_Domain\Event;
```

**Rules:**

- One blank line before the declaration, at least one blank line after
- Only one namespace declaration per file, at the top
- No curly brace syntax
- No global namespace declarations
- Use unique, long prefixes like `Vendor\Project_Name` to prevent conflicts
- Do NOT use `wp` or `WordPress` as namespace prefixes

**Note:** Namespaces are encouraged for plugins/themes but not yet used in WordPress Core.

### 9.2 Import Use Statements

**Impact: MEDIUM (code organization)**

Import `use` statements should be at the top of the file after the namespace declaration.

**Order of imports:**

1. Namespaces, classes, interfaces, traits, enums
2. Functions
3. Constants

**Incorrect:**

```php
namespace Project_Name\Feature;

use const Project_Name\Sub_Feature\CONSTANT_A;  // Constants before classes
use function Project_Name\Sub_Feature\function_a;  // Functions before classes
use \Project_Name\Sub_Feature\Class_C as aliased_class_c;  // Leading backslash, wrong alias naming

class Foo {
    // Code.
}

use Project_Name\Another_Class;  // Import after class definition - not allowed
```

**Correct:**

```php
namespace Project_Name\Feature;

use Project_Name\Sub_Feature\Class_A;
use Project_Name\Sub_Feature\Class_C as Aliased_Class_C;
use Project_Name\Sub_Feature\{
    Class_D,
    Class_E as Aliased_Class_E,
}

use function Project_Name\Sub_Feature\function_a;
use function Project_Name\Sub_Feature\function_b as aliased_function;

use const Project_Name\Sub_Feature\CONSTANT_A;
use const Project_Name\Sub_Feature\CONSTANT_D as ALIASED_CONSTANT;

// Rest of the code.
```

**Rules:**

- No leading backslash in imports
- Aliases must follow WordPress naming conventions (capitalized words with underscores for classes, lowercase with underscores for functions)
- Don't combine different import types in one statement
- All imports before any class/function definitions

**Note:** Import `use` statements are discouraged in WordPress Core for now.

### 9.3 Trait Use Statements

**Impact: MEDIUM (OOP organization)**

Trait `use` statements should be at the top of a class with proper spacing.

**Incorrect:**

```php
class Foo {
    // No blank line before trait use statement
    use Bar_Trait;

    use Foo_Trait, Bazinga_Trait{Bar_Trait::method_name insteadof Foo_Trait;  // Wrong formatting
    Bazinga_Trait::method_name as bazinga_method;
    };

    public $baz = true;  // Missing blank line after trait import
}
```

**Correct:**

```php
class Foo {

    use Bar_Trait;

    use Foo_Trait, Bazinga_Trait {
        Bar_Trait::method_name insteadof Foo_Trait;
        Bazinga_Trait::method_name as bazinga_method;
    }

    use Loopy_Trait {
        eat as protected;
    }

    public $baz = true;

    // Rest of class...
}
```

**Rules:**

- One blank line before the first `use` statement
- At least one blank line after the last `use` statement (exception: if class only contains trait imports)
- Each aliasing/conflict resolution on its own line
- Proper indentation inside curly braces

---

## 10. Shell Commands

**Impact: HIGH (security)**

Use of shell commands requires caution due to security implications.

**Never use the backtick operator:**

```php
// Incorrect - backtick operator is identical to shell_exec()
$output = `ls -la`;
```

**Why:** The backtick operator is equivalent to `shell_exec()`, and most hosts disable this function in `php.ini` for security reasons. It can lead to command injection vulnerabilities if user input is involved.

**If shell commands are absolutely necessary:**

- Use `escapeshellarg()` and `escapeshellcmd()` for any user-provided input
- Prefer WordPress functions that handle this safely when available
- Document why shell access is needed
