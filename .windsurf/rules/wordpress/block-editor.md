---
trigger: glob
globs:
  [
    "**/*block.js",
    "**/block.json",
    "**/blocks/**/*.js",
    "**/blocks/**/*.jsx",
    "**/block-editor/**/*.js",
    "**/gutenberg/**/*.js",
  ]
description: WordPress Block Editor (Gutenberg) development standards. Auto-applies when working with block-related files.
---

# WordPress Block Editor (Gutenberg) Standards

Comprehensive guidelines for developing WordPress blocks and extending the Block Editor.

**References:**

- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
- [Component Reference](https://developer.wordpress.org/block-editor/reference-guides/components/)
- [Data Module Reference](https://developer.wordpress.org/block-editor/reference-guides/data/)

---

## Critical Rules

### Use block.json for Registration

Always register blocks using `block.json` metadata file. This is the canonical method since WordPress 5.8.

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "my-plugin/my-block",
	"title": "My Block",
	"category": "widgets",
	"icon": "star",
	"description": "A custom block.",
	"keywords": ["custom", "block"],
	"textdomain": "my-plugin",
	"attributes": {
		"content": {
			"type": "string",
			"source": "html",
			"selector": ".content"
		}
	},
	"supports": {
		"align": true,
		"html": false
	},
	"editorScript": "file:./index.js",
	"editorStyle": "file:./index.css",
	"style": "file:./style.css",
	"render": "file:./render.php"
}
```

**Benefits:**

- Lazy loading of assets (only loads when block is present)
- Block Directory recognition
- Server-side registration via REST API
- Schema validation in editors

### Use API Version 3

Always use `apiVersion: 3` (introduced in WordPress 6.3) for new blocks.

```json
{
	"apiVersion": 3
}
```

### Use useBlockProps Hook

Every block wrapper must use `useBlockProps` for proper editor integration.

```jsx
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return <div { ...blockProps }>{ /* Block content */ }</div>;
}
```

For custom classes or attributes:

```jsx
const blockProps = useBlockProps( {
	className: 'my-custom-class',
	'data-custom': 'value',
} );
```

---

## 1. Block Structure

### File Organization

```text
blocks/
└── my-block/
    ├── block.json          # Block metadata (required)
    ├── index.js            # Block registration
    ├── edit.js             # Editor component
    ├── save.js             # Save component (static blocks)
    ├── render.php          # Server-side render (dynamic blocks)
    ├── index.css           # Editor styles
    ├── style.css           # Frontend + editor styles
    └── view.js             # Frontend JavaScript
```

### Block Registration

```jsx
// index.js
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save,
} );
```

---

## 2. Edit Component

### Functional Components Only

Use functional components with hooks. Never use class components.

```jsx
// CORRECT
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const blockProps = useBlockProps();

	return <div { ...blockProps }>{ /* Content */ }</div>;
}

// INCORRECT - Class component
class Edit extends Component {
	render() {
		return <div>{ /* Content */ }</div>;
	}
}
```

### Destructure Props

Always destructure props at the function signature level.

```jsx
export default function Edit( {
	attributes,
	setAttributes,
	isSelected,
	clientId,
	context,
} ) {
	const { content, alignment } = attributes;
	// ...
}
```

### Update Attributes Immutably

Use `setAttributes` with object spread for updates.

```jsx
// CORRECT
setAttributes( { content: newContent } );
setAttributes( { ...attributes, items: [ ...items, newItem ] } );

// INCORRECT - Direct mutation
attributes.content = newContent;
```

---

## 3. Save Component

### Static vs Dynamic Blocks

**Static blocks:** Content saved to post content as HTML.

```jsx
// save.js
import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<p>{ attributes.content }</p>
		</div>
	);
}
```

**Dynamic blocks:** Content rendered server-side via PHP.

```jsx
// save.js - Return null for dynamic blocks
export default function save() {
	return null;
}
```

```php
// render.php
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $attributes['content'] ); ?>
</div>
```

### Block Wrapper in Save

Always use `useBlockProps.save()` in the save function.

```jsx
import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const blockProps = useBlockProps.save( {
		className: `align-${ attributes.alignment }`,
	} );

	return <div { ...blockProps }>{ attributes.content }</div>;
}
```

---

## 4. WordPress Packages

### Essential Packages

```jsx
// Block Editor
import {
	useBlockProps,
	RichText,
	InspectorControls,
	BlockControls,
	InnerBlocks,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';

// Components
import {
	PanelBody,
	TextControl,
	ToggleControl,
	SelectControl,
	Button,
	Spinner,
	Placeholder,
} from '@wordpress/components';

// Data
import { useSelect, useDispatch } from '@wordpress/data';

// Core utilities
import { __ } from '@wordpress/i18n';
import { useEffect, useState, useCallback } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
```

### Importing via WordPress Global

When not using a build process, access packages via `wp` global.

```javascript
const { useBlockProps } = wp.blockEditor;
const { Button } = wp.components;
const { __ } = wp.i18n;
```

### Package Dependencies

In PHP, declare script dependencies correctly:

```php
wp_enqueue_script(
	'my-block-editor',
	plugins_url( 'build/index.js', __FILE__ ),
	array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element' ),
	filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' )
);
```

---

## 5. Data Layer

### useSelect for Reading Data

```jsx
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

function MyComponent() {
	const posts = useSelect( ( select ) => {
		return select( coreStore ).getEntityRecords( 'postType', 'post', {
			per_page: 10,
		} );
	}, [] );

	if ( ! posts ) {
		return <Spinner />;
	}

	return (
		<ul>
			{ posts.map( ( post ) => (
				<li key={ post.id }>{ post.title.rendered }</li>
			) ) }
		</ul>
	);
}
```

### useDispatch for Writing Data

```jsx
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

function MyComponent() {
	const { createSuccessNotice } = useDispatch( noticesStore );

	const handleSave = () => {
		// Save logic
		createSuccessNotice( 'Saved successfully!' );
	};

	return <Button onClick={ handleSave }>Save</Button>;
}
```

### Available Data Stores

| Store               | Purpose                                   |
| ------------------- | ----------------------------------------- |
| `core`              | WordPress core data (posts, users, terms) |
| `core/block-editor` | Block editor state                        |
| `core/blocks`       | Block types registry                      |
| `core/editor`       | Post editor state                         |
| `core/notices`      | Admin notices                             |
| `core/preferences`  | User preferences                          |

---

## 6. Block Controls

### Inspector Controls (Sidebar)

```jsx
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { showTitle, columns } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'my-plugin' ) }>
					<ToggleControl
						label={ __( 'Show Title', 'my-plugin' ) }
						checked={ showTitle }
						onChange={ ( value ) => setAttributes( { showTitle: value } ) }
					/>
					<RangeControl
						label={ __( 'Columns', 'my-plugin' ) }
						value={ columns }
						onChange={ ( value ) => setAttributes( { columns: value } ) }
						min={ 1 }
						max={ 4 }
					/>
				</PanelBody>
			</InspectorControls>
			{ /* Block content */ }
		</>
	);
}
```

### Block Controls (Toolbar)

```jsx
import { BlockControls, AlignmentToolbar } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const { alignment } = attributes;

	return (
		<>
			<BlockControls>
				<AlignmentToolbar
					value={ alignment }
					onChange={ ( value ) => setAttributes( { alignment: value } ) }
				/>
			</BlockControls>
			{ /* Block content */ }
		</>
	);
}
```

---

## 7. Inner Blocks

### Basic Usage

```jsx
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InnerBlocks
				allowedBlocks={ [ 'core/paragraph', 'core/heading' ] }
				template={ [
					[ 'core/heading', { level: 2 } ],
					[ 'core/paragraph', { placeholder: 'Add content...' } ],
				] }
				templateLock={ false }
			/>
		</div>
	);
}

export function save() {
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<InnerBlocks.Content />
		</div>
	);
}
```

### Template Lock Options

| Value           | Behavior                                  |
| --------------- | ----------------------------------------- |
| `false`         | Blocks can be moved, added, removed       |
| `'all'`         | No changes allowed                        |
| `'insert'`      | Blocks can be moved but not added/removed |
| `'contentOnly'` | Only content editing allowed              |

---

## 8. Rich Text

```jsx
import { RichText, useBlockProps } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return (
		<RichText
			{ ...blockProps }
			tagName="p"
			value={ attributes.content }
			onChange={ ( content ) => setAttributes( { content } ) }
			placeholder={ __( 'Enter text...', 'my-plugin' ) }
			allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
		/>
	);
}

export function save( { attributes } ) {
	const blockProps = useBlockProps.save();

	return (
		<RichText.Content { ...blockProps } tagName="p" value={ attributes.content } />
	);
}
```

---

## 9. Block Supports

Define in `block.json` for automatic features:

```json
{
	"supports": {
		"align": ["wide", "full"],
		"anchor": true,
		"className": true,
		"color": {
			"background": true,
			"text": true,
			"gradients": true
		},
		"spacing": {
			"margin": true,
			"padding": true
		},
		"typography": {
			"fontSize": true,
			"lineHeight": true
		},
		"html": false,
		"reusable": true
	}
}
```

---

## 10. Block Filters

### Modify Block Registration

```jsx
import { addFilter } from '@wordpress/hooks';

addFilter(
	'blocks.registerBlockType',
	'my-plugin/modify-paragraph',
	( settings, name ) => {
		if ( name !== 'core/paragraph' ) {
			return settings;
		}

		return {
			...settings,
			supports: {
				...settings.supports,
				customClassName: false,
			},
		};
	},
);
```

### Extend Block Edit

```jsx
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';

const withCustomControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== 'core/paragraph' ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Custom Settings', 'my-plugin' ) }>
						{ /* Custom controls */ }
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withCustomControls' );

addFilter( 'editor.BlockEdit', 'my-plugin/custom-controls', withCustomControls );
```

---

## 11. Internationalization

### Translating Strings

```jsx
import { __, _n, sprintf } from '@wordpress/i18n';

// Simple string
const label = __( 'My Label', 'my-plugin' );

// Pluralization
const message = sprintf( _n( '%d item', '%d items', count, 'my-plugin' ), count );

// With variables
const greeting = sprintf( __( 'Hello, %s!', 'my-plugin' ), userName );
```

### In block.json

Translatable fields are automatically handled when `textdomain` is set.

```json
{
	"textdomain": "my-plugin",
	"title": "My Block",
	"description": "A custom block.",
	"keywords": ["custom"]
}
```

---

## 12. Build Process

### wp-scripts Commands

```bash
# Development with watch
npm start

# Production build
npm run build

# Linting
npm run lint:js
npm run lint:css

# Testing
npm run test:unit
```

### webpack Configuration

For custom config, extend `@wordpress/scripts`:

```javascript
// webpack.config.js
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		'custom-entry': './src/custom-entry.js',
	},
};
```

---

## 13. Performance

### Parallel Data Fetching

```jsx
// CORRECT - Parallel fetching
const { posts, categories } = useSelect( ( select ) => {
	const { getEntityRecords } = select( coreStore );
	return {
		posts: getEntityRecords( 'postType', 'post' ),
		categories: getEntityRecords( 'taxonomy', 'category' ),
	};
}, [] );

// INCORRECT - Sequential in separate hooks
const posts = useSelect( ( select ) => /* ... */ );
const categories = useSelect( ( select ) => /* ... */ );
```

### Selector Dependencies

```jsx
// CORRECT - Minimal dependencies
const postTitle = useSelect(
	( select ) => {
		return select( coreStore ).getEntityRecord( 'postType', 'post', postId )?.title;
	},
	[ postId ],
);

// INCORRECT - Returns entire object, causes re-renders
const post = useSelect(
	( select ) => {
		return select( coreStore ).getEntityRecord( 'postType', 'post', postId );
	},
	[ postId ],
);
```

### Dynamic Imports

```jsx
import { lazy, Suspense } from '@wordpress/element';

const HeavyComponent = lazy( () => import( './HeavyComponent' ) );

function MyBlock() {
	return (
		<Suspense fallback={ <Spinner /> }>
			<HeavyComponent />
		</Suspense>
	);
}
```

---

## VIP Standards

For WordPress VIP-specific block editor standards including dynamic blocks, block bindings, Script Modules API, and enterprise performance patterns, see `wordpress-vip/wpvip-block-editor.md`.

---

## Tooling

```bash
# Create new block
npx @wordpress/create-block my-block

# Create block in existing plugin
npx @wordpress/create-block my-block --no-plugin

# With interactive mode
npx @wordpress/create-block

# Install packages
npm install @wordpress/scripts --save-dev
npm install @wordpress/block-editor @wordpress/blocks @wordpress/components @wordpress/i18n
```
