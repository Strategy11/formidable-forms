# Naming Conventions

**Priority: HIGH**  
**Impact: Code consistency and readability**

---

## Variables and Functions

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

---

## Classes, Interfaces, Traits, Enums

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

---

## Constants

All uppercase with underscores separating words.

**Correct:**

```php
define( 'DOING_AJAX', true );
const MAX_UPLOAD_SIZE = 1048576;
```

---

## File Naming

Use lowercase letters with hyphens separating words.

**General files:**

```text
my-plugin-name.php
template-parts.php
```

**Class files (prefix with `class-`):**

```text
class-wp-error.php       // For WP_Error class
class-walker-category.php // For Walker_Category class
```

**Template tags (suffix with `-template`):**

```text
general-template.php
```

---

## Dynamic Hooks

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
