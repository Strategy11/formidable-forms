# Formatting

**Priority: HIGH**  
**Impact: Code structure and maintainability**

---

## Brace Style

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

---

## Array Syntax

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

---

## Multiline Function Calls

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

---

## Type Declarations

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
