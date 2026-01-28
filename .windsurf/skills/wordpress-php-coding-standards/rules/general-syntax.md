# General Syntax

**Priority: LOW**  
**Impact: Compatibility and string handling**

---

## PHP Tags

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

---

## Quotes

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

---

## Require/Include

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
