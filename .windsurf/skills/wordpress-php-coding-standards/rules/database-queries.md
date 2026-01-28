# Database Queries

**Priority: CRITICAL**  
**Impact: Prevents SQL injection**

---

## Overview

Database interactions must be secure and properly escaped to prevent SQL injection. Avoid touching the database directly—use WordPress functions when available. If you must write queries, always use `$wpdb->prepare()`.

---

## Rules

### Always Use $wpdb->prepare()

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

---

## Placeholders

- `%d` — integer
- `%f` — float
- `%s` — string
- `%i` — identifier (table/field names)

**Important:** Do not quote placeholders! `$wpdb->prepare()` handles escaping and quoting.

---

## SQL Formatting

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
