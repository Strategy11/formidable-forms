---
trigger: glob
globs: ["**/*.php", "**/*.js"]
description: WordPress VIP security standards based on OWASP guidelines. Auto-applies to PHP and JS files.
---

# WordPress VIP Security Standards

Enterprise-level security for WordPress VIP platform based on OWASP Top 10.

**Reference:** [WordPress VIP Learn - Enterprise Security](https://learn.wpvip.com/)

---

## XSS Prevention

### Escape Late

Escape data immediately before output, not at assignment.

```php
// CORRECT: Escape at output
echo esc_html( $title );

// INCORRECT: Escape at assignment
$title = esc_html( $raw_title );
// ... later
echo $title; // May be double-escaped or bypassed
```

### Escaping Functions

| Function         | Use Case             |
| ---------------- | -------------------- |
| `esc_html()`     | HTML element content |
| `esc_attr()`     | HTML attributes      |
| `esc_url()`      | URLs and links       |
| `esc_js()`       | Inline JavaScript    |
| `esc_textarea()` | Textarea content     |
| `wp_kses_post()` | Allow safe HTML      |
| `wp_kses()`      | Custom allowed HTML  |

### Output Examples

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

### JavaScript XSS Prevention

```php
// Pass data to JavaScript safely
wp_localize_script( 'my-script', 'myData', array(
	'title' => esc_js( $title ),
	'data'  => wp_json_encode( $data ),
) );
```

```javascript
// UNSAFE: Never use
element.innerHTML = userData;
$(element).html(userData);
eval(userInput);

// SAFE: Programmatic DOM manipulation
const text = document.createTextNode(userData);
element.appendChild(text);

// SAFE: Use textContent for text
element.textContent = userData;
```

---

## SQL Injection Prevention

### Always Use Prepare

```php
$wpdb->query(
	$wpdb->prepare(
		"UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d",
		$title,
		$id
	)
);
```

### Prepare Placeholders

| Placeholder | Type                           |
| ----------- | ------------------------------ |
| `%d`        | Integer                        |
| `%f`        | Float                          |
| `%s`        | String                         |
| `%i`        | Identifier (table/field names) |

### Use WordPress APIs

```php
// PREFERRED: WordPress functions
$posts = get_posts( array(
	'post_type'      => 'post',
	'posts_per_page' => 10,
) );

// AVOID: Direct queries when possible
$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts LIMIT 10" );
```

---

## Input Sanitization

### Sanitize Early

Sanitize all input immediately when reading from superglobals.

**For Formidable:** Use `FrmAppHelper::get_post_param()`, `FrmAppHelper::simple_get()`, or `FrmAppHelper::get_param()` instead of direct `$_POST`/`$_GET` access. See `formidable/frm-security.md` for details.

### Sanitization Functions

| Function                    | Use Case               |
| --------------------------- | ---------------------- |
| `sanitize_text_field()`     | Single line text       |
| `sanitize_textarea_field()` | Multi-line text        |
| `sanitize_email()`          | Email addresses        |
| `sanitize_file_name()`      | File names             |
| `sanitize_key()`            | Keys and slugs         |
| `sanitize_title()`          | Titles and slugs       |
| `sanitize_url()`            | URLs                   |
| `absint()`                  | Positive integers      |
| `intval()`                  | Integers               |
| `wp_kses()`                 | HTML with allowed tags |

---

## Input Validation

### Validate Against Trusted Values

```php
// CORRECT: Validate against allowed list
$allowed = array( 'draft', 'publish', 'pending' );
if ( ! in_array( $status, $allowed, true ) ) {
	wp_die( 'Invalid status' );
}

// Use strict comparison
if ( $value === 'expected' ) {
	// Process
}
```

---

## File Operations

### Use WP_Filesystem

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
```

### Upload Validation

```php
$upload = wp_handle_upload(
	$_FILES['file'],
	array(
		'test_form' => false,
		'mimes'     => array(
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
			'pdf'      => 'application/pdf',
		),
	)
);

if ( isset( $upload['error'] ) ) {
	wp_die( $upload['error'] );
}
```

---

## Remote Requests

### Use WordPress HTTP API

```php
$response = wp_safe_remote_get( 'https://api.example.com/data', array(
	'timeout' => 15,
) );

if ( is_wp_error( $response ) ) {
	error_log( 'API Error: ' . $response->get_error_message() );
	return false;
}

$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );
```

### Never Disable SSL Verification

```php
// NEVER do this in production
$response = wp_remote_get( $url, array(
	'sslverify' => false, // DANGEROUS
) );
```

---

## Forbidden Functions

| Function                       | Reason                 | Alternative                            |
| ------------------------------ | ---------------------- | -------------------------------------- |
| `extract()`                    | Unpredictable          | Access array keys directly             |
| `eval()`                       | Security vulnerability | Refactor code logic                    |
| `create_function()`            | Deprecated, insecure   | Anonymous functions                    |
| `unserialize()`                | Object injection risk  | `json_decode()` or validate input type |
| `file_get_contents()` for URLs | Unreliable             | `wp_safe_remote_get()`                 |
| `curl_*` functions             | Inconsistent           | WP HTTP API                            |

**For Formidable:** Use `FrmAppHelper::maybe_unserialize_array()` instead of `unserialize()`. Note that `serialize()` is safe to use.

---

## Error Handling

### No Error Suppression

```php
// INCORRECT
$value = @file_get_contents( $file );

// CORRECT
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

### Never Log Sensitive Data

```php
// INCORRECT
error_log( 'Login: ' . $username . ' / ' . $password );

// CORRECT
error_log( 'Login attempt: ' . $username );
```
