---
trigger: glob
globs:
  [
    '**/blocks/**/*.js',
    '**/blocks/**/*.jsx',
    '**/block-editor/**/*.js',
    '**/gutenberg/**/*.js',
    '**/*.block.js',
    '**/block.json',
  ]
description: WordPress VIP Block Editor standards for enterprise block development. Auto-applies when working with block-related files.
---

# WordPress VIP Block Editor Standards

Enterprise-level guidelines for developing WordPress blocks on the VIP platform.

**Reference:** [WordPress VIP Learn - Enterprise Block Editor](https://learn.wpvip.com/)

---

## Dynamic Blocks

Dynamic blocks generate content server-side using PHP, ideal for frequently updated data.

### render Property Method

```json
{
	"apiVersion": 3,
	"name": "my-plugin/dynamic-block",
	"render": "file:./render.php"
}
```

```php
<?php
// render.php - receives $attributes, $content, $block
$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
	<?php echo esc_html( $attributes['title'] ); ?>
</div>
```

### render_callback Method

```php
function render_my_block( $attributes, $content, $block ) {
	$wrapper_attributes = get_block_wrapper_attributes();
	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		esc_html( $attributes['title'] )
	);
}

register_block_type( __DIR__ . '/build', array(
	'render_callback' => 'render_my_block',
) );
```

### Static vs Dynamic Decision

| Use Static           | Use Dynamic                       |
| -------------------- | --------------------------------- |
| Simple content       | Frequently changing data          |
| Performance priority | External API data                 |
| No external data     | Code updates affect all instances |
| Consistent output    | Complex server-side logic         |

---

## Block Bindings

Block bindings dynamically populate content from data sources (WordPress 6.5+).

### Using Custom Fields

```php
// Register meta field
register_meta( 'post', 'custom_subtitle', array(
	'show_in_rest' => true,
	'single'       => true,
	'type'         => 'string',
) );
```

```html
<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"custom_subtitle"}}}}} -->
<p></p>
<!-- /wp:paragraph -->
```

### Custom Binding Source

```php
register_block_bindings_source( 'my-plugin/custom-source', array(
	'label'              => __( 'Custom Source', 'my-plugin' ),
	'get_value_callback' => function( $source_args, $block_instance, $attribute_name ) {
		return get_option( $source_args['key'], '' );
	},
	'uses_context'       => array( 'postId' ),
) );
```

---

## Script Modules API

Native ESM support in WordPress 6.5+ for optimized loading.

### viewScriptModule in block.json

```json
{
	"apiVersion": 3,
	"name": "my-plugin/interactive-block",
	"viewScriptModule": "file:./view.js"
}
```

### Register Script Module

```php
wp_register_script_module(
	'@my-plugin/feature',
	plugin_dir_url( __FILE__ ) . 'build/feature.js'
);

wp_enqueue_script_module(
	'@my-plugin/main',
	plugin_dir_url( __FILE__ ) . 'build/main.js',
	array( '@my-plugin/feature' )
);
```

### Dynamic Import

```javascript
// Load on demand
document.getElementById( 'trigger' ).addEventListener( 'click', async () => {
	const { initialize } = await import( '@my-plugin/heavy-feature' );
	initialize();
} );
```

---

## Block Categories

### Create Custom Category

```php
add_filter( 'block_categories_all', function( $categories, $post ) {
	return array_merge(
		array(
			array(
				'slug'  => 'my-plugin',
				'title' => __( 'My Plugin', 'my-plugin' ),
				'icon'  => 'star-filled',
			),
		),
		$categories
	);
}, 10, 2 );
```

### Assign Block to Category

```json
{
	"name": "my-plugin/custom-block",
	"category": "my-plugin"
}
```

---

## Security in Blocks

### Validate Attributes

```jsx
const updateTitle = ( newTitle ) => {
	if ( newTitle.length > 100 ) {
		return; // Reject invalid input
	}
	setAttributes( { title: newTitle } );
};
```

### Escape Output in Save

```jsx
import { escapeHTML, escapeAttribute } from '@wordpress/escape-html';

export default function save( { attributes } ) {
	return (
		<div { ...useBlockProps.save() }>
			<h2>{ escapeHTML( attributes.title ) }</h2>
		</div>
	);
}
```

### PHP Escaping in render.php

```php
<div <?php echo get_block_wrapper_attributes(); ?>>
	<h2><?php echo esc_html( $attributes['title'] ); ?></h2>
	<a href="<?php echo esc_url( $attributes['link'] ); ?>">
		<?php echo esc_html( $attributes['linkText'] ); ?>
	</a>
</div>
```

### Capability Checks

```jsx
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

function RestrictedControl() {
	const canEdit = useSelect( ( select ) => {
		return select( coreStore ).canUser( 'update', 'settings' );
	}, [] );

	if ( ! canEdit ) {
		return null;
	}

	return <AdminOnlyControl />;
}
```

---

## Accessibility in Blocks

### Semantic HTML

```jsx
// Use semantic elements
<nav aria-label={ __( 'Main Navigation', 'my-plugin' ) }>
	{ /* Navigation content */ }
</nav>

<main role="main">
	{ /* Main content */ }
</main>
```

### Keyboard Support

```jsx
function CustomButton( { onClick, children } ) {
	return (
		<button
			onClick={ onClick }
			onKeyDown={ ( event ) => {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					onClick();
				}
			} }
		>
			{ children }
		</button>
	);
}
```

### ARIA Live Regions

```jsx
import { useState } from '@wordpress/element';

function DynamicContent() {
	const [ status, setStatus ] = useState( '' );

	return (
		<div aria-live="polite" aria-atomic="true">
			{ status }
		</div>
	);
}
```

---

## Performance Optimization

### Code Splitting

```javascript
// webpack.config.js
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		main: path.resolve( process.cwd(), 'resources/js/module.js' ),
		editor: path.resolve( process.cwd(), 'resources/js/editor.js' ),
	},
};
```

### Caching in Data Layer

```jsx
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

// CORRECT - Minimal dependencies, automatic caching
const postTitle = useSelect(
	( select ) => {
		return select( coreStore ).getEntityRecord( 'postType', 'post', postId )?.title;
	},
	[ postId ]
);
```

---

## Tooling

```bash
# Create new block
npx @wordpress/create-block my-block

# Build with modules support
npm run build -- --experimental-modules

# Install VIP-compatible packages
npm install @wordpress/scripts --save-dev
```
