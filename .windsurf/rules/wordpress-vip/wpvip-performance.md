---
trigger: glob
globs: ['**/*.php']
description: WordPress VIP performance optimization standards. Auto-applies to PHP files.
---

# WordPress VIP Performance Standards

Enterprise-level performance optimization for WordPress VIP platform.

**Reference:** [WordPress VIP Learn - Enterprise Performance](https://learn.wpvip.com/)

---

## MySQL Query Optimization

### Query Count

Each SQL query adds overhead and latency. Reduce the number of queries.

```php
// Use Query Monitor plugin to identify unnecessary queries
// Target: < 50 queries per page load
```

### Query Design

```php
// CORRECT: Efficient query with proper limits
$posts = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT ID, post_title FROM $wpdb->posts
			WHERE post_status = %s
			AND post_type = %s
			LIMIT %d",
		'publish',
		'post',
		100
	)
);

// INCORRECT: Unbounded query
$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts" );
```

### Efficient WP_Query

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

### Avoid Meta Queries at Scale

Meta queries do not scale. Consider alternatives:

- Custom tables for high-volume data
- Taxonomy terms for filterable data
- Caching query results

---

## Caching Strategies

### Full Page Caching

Pages can be cached and served directly without regenerating content.

| Cacheable                    | Not Cacheable       |
| ---------------------------- | ------------------- |
| Static pages                 | User-specific data  |
| Blog posts                   | Shopping carts      |
| Category archives            | Account pages       |
| Content that changes rarely  | Real-time data      |

### Object Cache

```php
$data = wp_cache_get( 'my_cache_key', 'my_group' );

if ( false === $data ) {
	$data = expensive_operation();
	wp_cache_set( 'my_cache_key', $data, 'my_group', HOUR_IN_SECONDS );
}

return $data;
```

### Transients

```php
$data = get_transient( 'my_transient_key' );

if ( false === $data ) {
	$data = expensive_operation();
	set_transient( 'my_transient_key', $data, HOUR_IN_SECONDS );
}

return $data;
```

### Partial Output Caching

Cache static parts of pages when full page caching is not possible.

```php
$cache_key = sprintf( 'footer_output_%s', get_locale() );
$output    = wp_cache_get( $cache_key, 'partials' );

if ( false === $output ) {
	ob_start();
	get_template_part( 'template-parts/footer' );
	$output = ob_get_clean();
	wp_cache_set( $cache_key, $output, 'partials', DAY_IN_SECONDS );
}

echo $output;
```

### Cache Invalidation

```php
add_action( 'save_post', function( $post_id ) {
	delete_transient( 'posts_cache' );
	wp_cache_delete( 'posts_list', 'my_plugin' );
} );
```

---

## Template Optimization

### Avoid Template Over-use

Each template requires additional processing and database queries.

```php
// CAUTION: Too many template parts
get_template_part( 'header' );
get_template_part( 'nav' );
get_template_part( 'sidebar' );
get_template_part( 'content' );
get_template_part( 'footer' );

// BETTER: Cache reusable partials
$nav = wp_cache_get( 'main_nav', 'partials' );
if ( false === $nav ) {
	ob_start();
	get_template_part( 'nav' );
	$nav = ob_get_clean();
	wp_cache_set( 'main_nav', $nav, 'partials', HOUR_IN_SECONDS );
}
echo $nav;
```

---

## Hook Performance

### Avoid Excessive Callbacks

Only use necessary hooks and avoid excessive callbacks.

```php
// INCORRECT: Running on every admin request
add_action( 'admin_init', 'expensive_operation' );

// CORRECT: Conditional execution
add_action( 'admin_init', function() {
	if ( ! isset( $_GET['my_action'] ) ) {
		return;
	}
	expensive_operation();
} );
```

### Cache Filter Results

Filters are not cached by default.

```php
add_filter( 'expensive_filter', function( $value ) {
	$cache_key = 'filter_result_' . md5( serialize( $value ) );
	$cached    = wp_cache_get( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$result = process_value( $value );
	wp_cache_set( $cache_key, $result, '', HOUR_IN_SECONDS );

	return $result;
} );
```

---

## WP Cron Optimization

### Disable Front-end Cron

```php
// wp-config.php
define( 'DISABLE_WP_CRON', true );
```

### Use Server Cron

```bash
# Crontab entry: run every minute
* * * * * cd /path/to/wordpress && wp cron event run --due-now
```

### VIP Cron Control

On WordPress VIP, cron is handled by Automattic Cron Control plugin automatically.

---

## Batch Processing

### Avoid Loading All Posts

```php
// INCORRECT: Loads all posts into memory
$posts = get_posts( array( 'numberposts' => -1 ) );
foreach ( $posts as $post ) {
	process( $post );
}

// CORRECT: Batch processing
$paged = 1;
do {
	$posts = get_posts(
		array(
			'numberposts' => 100,
			'paged'       => $paged,
		)
	);

	foreach ( $posts as $post ) {
		process( $post );
	}

	$paged++;
} while ( count( $posts ) === 100 );
```

### Avoid Remote Requests in Loops

```php
// INCORRECT: Multiple requests
foreach ( $items as $item ) {
	$data = wp_remote_get( $item['url'] );
}

// CORRECT: Batch or background process
wp_schedule_single_event( time(), 'process_items_batch', array( $items ) );
```

---

## Asset Loading

### Conditional Loading

```php
add_action( 'wp_enqueue_scripts', function() {
	// Only load on specific pages
	if ( is_singular( 'product' ) ) {
		wp_enqueue_script( 'product-gallery' );
	}

	// Only load when shortcode is present
	global $post;
	if ( has_shortcode( $post->post_content, 'my_shortcode' ) ) {
		wp_enqueue_script( 'my-shortcode-script' );
	}
} );
```

### Defer Non-Critical Scripts

```php
add_filter( 'script_loader_tag', function( $tag, $handle ) {
	$defer_scripts = array( 'analytics', 'tracking' );

	if ( in_array( $handle, $defer_scripts, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}, 10, 2 );
```
