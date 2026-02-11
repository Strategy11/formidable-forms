---
trigger: glob
globs: ["**/*.php"]
description: WordPress VIP coding standards for performance and security. Auto-applies to PHP files.
---

# WordPress VIP Standards

WordPress VIP platform requirements for performance, security, and scalability.

**Reference:** [WordPress VIP Documentation](https://docs.wpvip.com/)

---

## 1. Database Queries

### Use Proper Methods

Never use `$wpdb->query()` for SELECT statements. Use specific methods.

| Method | Use Case |
|--------|----------|
| `$wpdb->get_results()` | Multiple rows |
| `$wpdb->get_row()` | Single row |
| `$wpdb->get_var()` | Single value |
| `$wpdb->get_col()` | Single column |

```php
// Correct
$posts = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_status = %s",
        'publish'
    )
);

// Incorrect
$posts = $wpdb->query( "SELECT * FROM $wpdb->posts" );
```

### Always Use Prepare

Always use `$wpdb->prepare()` for queries with variables.

```php
$wpdb->query(
    $wpdb->prepare(
        "UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d",
        $title,
        $id
    )
);
```

### Limit Results

Always include LIMIT clause to prevent unbounded queries.

```php
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_type = %s LIMIT %d",
        'page',
        100
    )
);
```

### Avoid Direct Queries When Possible

Use WordPress functions instead of direct queries.

```php
// Prefer this
$posts = get_posts( array(
    'post_type'      => 'post',
    'posts_per_page' => 10,
) );

// Over direct query
$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts LIMIT 10" );
```

---

## 2. Forbidden Functions

Never use these functions:

| Function | Reason | Alternative |
|----------|--------|-------------|
| `extract()` | Makes code unpredictable | Access array keys directly |
| `eval()` | Security vulnerability | Refactor code logic |
| `create_function()` | Deprecated, insecure | Use anonymous functions |
| `compact()` | Reduces readability | Build arrays explicitly |
| `file_get_contents()` for URLs | Unreliable, no error handling | `wp_remote_get()` |
| `file_put_contents()` | Direct file access | WP_Filesystem |
| `curl_*` functions | Inconsistent behavior | WP HTTP API |
| `serialize()`/`unserialize()` for user data | Security risk | `json_encode()`/`json_decode()` |

---

## 3. Remote Requests

### HTTP API

Use WordPress HTTP API for all remote requests.

```php
// GET request
$response = wp_remote_get( 'https://api.example.com/data' );

if ( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    // Handle error
} else {
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
}

// POST request
$response = wp_remote_post(
    'https://api.example.com/submit',
    array(
        'body' => array(
            'key' => 'value',
        ),
        'timeout' => 30,
    )
);
```

### Timeouts

Always set appropriate timeouts.

```php
$response = wp_remote_get(
    $url,
    array(
        'timeout' => 15,
    )
);
```

### SSL Verification

Never disable SSL verification in production.

```php
// INCORRECT - Never do this
$response = wp_remote_get(
    $url,
    array(
        'sslverify' => false,
    )
);
```

---

## 4. File Operations

### WP_Filesystem

Use WP_Filesystem for file operations.

```php
global $wp_filesystem;

if ( ! function_exists( 'WP_Filesystem' ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}

WP_Filesystem();

// Read file
$content = $wp_filesystem->get_contents( $file_path );

// Write file
$wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE );

// Check if file exists
if ( $wp_filesystem->exists( $file_path ) ) {
    // File exists
}
```

### Upload Handling

```php
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}

$upload = wp_handle_upload(
    $_FILES['file'],
    array(
        'test_form' => false,
        'mimes'     => array(
            'jpg|jpeg' => 'image/jpeg',
            'png'      => 'image/png',
        ),
    )
);

if ( isset( $upload['error'] ) ) {
    // Handle error
}
```

---

## 5. Caching

### Transients

Use transients for cached data.

```php
$data = get_transient( 'my_cache_key' );

if ( false === $data ) {
    $data = expensive_operation();
    set_transient( 'my_cache_key', $data, HOUR_IN_SECONDS );
}

return $data;
```

### Object Cache

Use object cache for frequently accessed data.

```php
$data = wp_cache_get( 'key', 'group' );

if ( false === $data ) {
    $data = expensive_operation();
    wp_cache_set( 'key', $data, 'group', HOUR_IN_SECONDS );
}

return $data;
```

### Cache Keys

Use descriptive and unique cache keys.

```php
$cache_key = sprintf( 'user_posts_%d_%s', $user_id, $post_type );
```

### Cache Invalidation

Invalidate cache when data changes.

```php
add_action( 'save_post', function( $post_id ) {
    delete_transient( 'posts_cache' );
    wp_cache_delete( 'posts_list', 'my_plugin' );
} );
```

---

## 6. Escaping Output

### Escape Late

Escape data at output, not at assignment.

```php
// Correct - escape at output
echo esc_html( $title );

// Incorrect - escape at assignment then output later
$title = esc_html( $raw_title );
// ... later
echo $title; // Might be double-escaped or bypassed
```

### Escaping Functions

| Function | Use Case |
|----------|----------|
| `esc_html()` | HTML element content |
| `esc_attr()` | HTML attributes |
| `esc_url()` | URLs |
| `esc_js()` | Inline JavaScript |
| `esc_textarea()` | Textarea content |
| `wp_kses_post()` | Allow safe HTML |
| `wp_kses()` | Custom allowed HTML |

```php
<div class="<?php echo esc_attr( $class ); ?>">
    <h2><?php echo esc_html( $title ); ?></h2>
    <a href="<?php echo esc_url( $url ); ?>">
        <?php echo esc_html( $link_text ); ?>
    </a>
    <div class="content">
        <?php echo wp_kses_post( $content ); ?>
    </div>
</div>
```

### Translation with Escaping

```php
echo esc_html__( 'Translated text', 'textdomain' );
echo esc_attr__( 'Attribute text', 'textdomain' );
printf(
    esc_html__( 'Hello, %s!', 'textdomain' ),
    esc_html( $name )
);
```

---

## 7. Sanitizing Input

### Sanitize Early

Sanitize user input as early as possible.

```php
$title = sanitize_text_field( wp_unslash( $_POST['title'] ) );
$email = sanitize_email( $_POST['email'] );
$url   = esc_url_raw( $_POST['url'] );
$key   = sanitize_key( $_POST['key'] );
$ids   = array_map( 'absint', $_POST['ids'] );
```

### Sanitization Functions

| Function | Use Case |
|----------|----------|
| `sanitize_text_field()` | Single line text |
| `sanitize_textarea_field()` | Multi-line text |
| `sanitize_email()` | Email addresses |
| `sanitize_file_name()` | File names |
| `sanitize_key()` | Keys and slugs |
| `sanitize_title()` | Titles and slugs |
| `absint()` | Positive integers |
| `intval()` | Integers |
| `wp_kses()` | HTML with allowed tags |

### Nonce Verification

Always verify nonces for form submissions.

```php
// In form
wp_nonce_field( 'my_action', 'my_nonce' );

// On submission
if ( ! isset( $_POST['my_nonce'] ) ||
     ! wp_verify_nonce( $_POST['my_nonce'], 'my_action' ) ) {
    wp_die( 'Security check failed' );
}
```

### Capability Checks

Always verify user capabilities.

```php
if ( ! current_user_can( 'edit_posts' ) ) {
    wp_die( 'Unauthorized access' );
}
```

---

## 8. Query Optimization

### Avoid Meta Queries on Large Tables

Meta queries do not scale. Consider alternatives:

- Custom tables for high-volume data
- Taxonomy terms for filterable data
- Caching query results

### Efficient Post Queries

```php
$args = array(
    'post_type'              => 'post',
    'posts_per_page'         => 10,
    'no_found_rows'          => true,  // Skip pagination count
    'update_post_meta_cache' => false, // Skip meta cache if not needed
    'update_post_term_cache' => false, // Skip term cache if not needed
    'fields'                 => 'ids', // Only get IDs if that is all you need
);

$query = new WP_Query( $args );
```

### Avoid posts_per_page = -1

Never get all posts without limit.

```php
// Incorrect
$args = array(
    'posts_per_page' => -1,
);

// Correct - use reasonable limit
$args = array(
    'posts_per_page' => 100,
);
```

### Use Proper Indexing

Ensure custom queries use indexed columns.

---

## 9. Error Handling

### No Error Suppression

Never use `@` operator.

```php
// Incorrect
$value = @file_get_contents( $file );

// Correct
if ( file_exists( $file ) && is_readable( $file ) ) {
    $value = file_get_contents( $file );
} else {
    $value = false;
}
```

### Proper Error Checking

```php
$result = some_operation();

if ( is_wp_error( $result ) ) {
    error_log( 'Operation failed: ' . $result->get_error_message() );
    return false;
}

return $result;
```

### Try-Catch for Exceptions

```php
try {
    $result = risky_operation();
} catch ( Exception $e ) {
    error_log( 'Exception: ' . $e->getMessage() );
    return new WP_Error( 'operation_failed', $e->getMessage() );
}
```

---

## 10. Performance Best Practices

### Lazy Loading

Load resources only when needed.

```php
add_action( 'wp_enqueue_scripts', function() {
    if ( is_singular( 'product' ) ) {
        wp_enqueue_script( 'product-gallery' );
    }
} );
```

### Avoid Loading All Posts

```php
// Incorrect - loads all posts into memory
$posts = get_posts( array( 'numberposts' => -1 ) );
foreach ( $posts as $post ) {
    // Process
}

// Correct - batch processing
$paged = 1;
do {
    $posts = get_posts( array(
        'numberposts' => 100,
        'paged'       => $paged,
    ) );

    foreach ( $posts as $post ) {
        // Process
    }

    $paged++;
} while ( count( $posts ) === 100 );
```

### Avoid Remote Requests in Loops

```php
// Incorrect
foreach ( $items as $item ) {
    $data = wp_remote_get( $item['url'] );
}

// Correct - batch or cache
$cached = get_transient( 'batch_data' );
if ( false === $cached ) {
    // Make single batch request or queue for background processing
}
```

---

## 11. Background Processing

### WP Cron

```php
// Schedule event
if ( ! wp_next_scheduled( 'my_cron_event' ) ) {
    wp_schedule_event( time(), 'hourly', 'my_cron_event' );
}

// Handle event
add_action( 'my_cron_event', 'my_cron_function' );
function my_cron_function() {
    // Perform background task
}

// Unschedule on deactivation
register_deactivation_hook( __FILE__, function() {
    wp_clear_scheduled_hook( 'my_cron_event' );
} );
```

### Action Scheduler

For more complex background jobs, use Action Scheduler library.

---

## 12. Logging

### Use Error Log

```php
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Debug message: ' . print_r( $data, true ) );
}
```

### Never Log Sensitive Data

```php
// INCORRECT - logs password
error_log( 'User login: ' . $username . ' / ' . $password );

// Correct
error_log( 'User login attempt: ' . $username );
```

---

## Tooling

```bash
# Install VIP Coding Standards
composer require automattic/vipwpcs

# Configure phpcs.xml
<ruleset name="VIP">
    <rule ref="WordPress-VIP-Go"/>
</ruleset>

# Run check
./vendor/bin/phpcs --standard=WordPress-VIP-Go path/to/file.php
```
