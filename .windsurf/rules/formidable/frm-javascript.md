---
trigger: glob
globs: ["**/*.js", "**/*.jsx", "**/*.mjs"]
description: Formidable Forms JavaScript patterns, modern ES6+ practices, and architectural decisions. Auto-applies to JS files.
---

# Formidable Forms JavaScript Patterns

JavaScript-specific patterns, coding standards, and architectural decisions for Formidable Forms. These extend the WordPress JavaScript Coding Standards with modern best practices.

---

## Critical Rules for New Code

### No jQuery

New code must NOT use jQuery. Use native DOM APIs and modern JavaScript.

```javascript
// INCORRECT: jQuery
$( '.my-element' ).addClass( 'active' );
$( '.my-element' ).on( 'click', handler );
$.ajax( { url: '/api' } );

// CORRECT: Native
document.querySelector( '.my-element' ).classList.add( 'active' );
document.querySelector( '.my-element' ).addEventListener( 'click', handler );
fetch( '/api' );
```

### Functional Over Class-Based

Prefer functional and modular implementations over classes.

```javascript
// INCORRECT: Class-based
class TemplateManager {
	constructor() {
		this.templates = [];
	}

	addTemplate( template ) {
		this.templates.push( template );
	}
}

// CORRECT: Functional/Modular
const createTemplateManager = () => {
	const templates = [];

	const addTemplate = ( template ) => {
		templates.push( template );
	};

	const getTemplates = () => [ ...templates ];

	return { addTemplate, getTemplates };
};
```

### ES6+ Modern Syntax

All new code must use ES6+ features. Target stable ECMAScript features only.

---

## 1. Variables and Declarations

### Use const and let

Never use `var`. Use `const` by default. Use `let` only when reassignment is needed.

```javascript
// CORRECT
const MAX_COUNT = 100;
const config = { timeout: 30 };
let currentIndex = 0;

// INCORRECT
var count = 0;
```

### One Variable Per Declaration

Each variable gets its own declaration statement.

```javascript
// CORRECT
const a = 1;
const b = 2;
let c = 3;

// INCORRECT
const a = 1,
	b = 2;
```

### Declare Close to First Use

Declare variables close to where they are first used, not at the top of functions.

```javascript
// CORRECT
function processItems( items ) {
	if ( ! items.length ) {
		return [];
	}

	const processed = [];
	for ( const item of items ) {
		const result = transform( item );
		processed.push( result );
	}

	return processed;
}
```

---

## 2. Functions

### Prefer Arrow Functions

Use arrow functions for callbacks and short functions.

```javascript
// CORRECT
const numbers = [ 1, 2, 3 ];
const doubled = numbers.map( ( n ) => n * 2 );

elements.forEach( ( element ) => {
	element.classList.add( 'active' );
} );

// Named function for complex logic
function processComplexData( data ) {
	// Multiple statements
}
```

### Pure Functions

Prefer pure functions without side effects.

```javascript
// CORRECT: Pure function
const calculateTotal = ( items ) =>
	items.reduce( ( sum, item ) => sum + item.price, 0 );

// INCORRECT: Impure function with side effects
let total = 0;
const calculateTotal = ( items ) => {
	items.forEach( ( item ) => {
		total += item.price; // Mutates external state
	} );
};
```

### Default Parameters

Use default parameters instead of conditional logic.

```javascript
// CORRECT
function createUser( name, role = 'subscriber' ) {
	return { name, role };
}

// INCORRECT
function createUser( name, role ) {
	role = role || 'subscriber';
	return { name, role };
}
```

### Rest Parameters

Use rest parameters instead of `arguments` object.

```javascript
// CORRECT
function sum( ...numbers ) {
	return numbers.reduce( ( total, n ) => total + n, 0 );
}

// INCORRECT
function sum() {
	return Array.prototype.slice.call( arguments ).reduce( ( t, n ) => t + n, 0 );
}
```

### Early Returns

Use early returns to avoid deep nesting.

```javascript
// CORRECT
function processUser( user ) {
	if ( ! user ) {
		return null;
	}

	if ( ! user.isActive ) {
		return { error: 'User inactive' };
	}

	// Do something else

	return { data: user.profile };
}

// INCORRECT
function processUser( user ) {
	if ( user ) {
		if ( user.isActive ) {
			// Do something else

			return { data: user.profile };
		} else {
			return { error: 'User inactive' };
		}
	} else {
		return null;
	}
}
```

---

## 3. Objects and Arrays

### Object Shorthand

Use shorthand property and method syntax.

```javascript
// CORRECT
const name = 'John';
const age = 30;
const user = {
	name,
	age,
	greet() {
		return `Hello, ${ this.name }`;
	},
};

// INCORRECT
const user = {
	name: name,
	age: age,
	greet: function () {
		return 'Hello, ' + this.name;
	},
};
```

### Destructuring

Use destructuring for objects and arrays.

```javascript
// CORRECT
const { name, email } = user;
const [ first, second ] = items;
const { data: userData } = response;

function processUser( { name, email, role = 'user' } ) {
	// Use destructured values
}

// INCORRECT
const name = user.name;
const email = user.email;
const first = items[ 0 ];
```

### Spread Operator

Use spread for copying and merging.

```javascript
// CORRECT
const newArray = [ ...oldArray, newItem ];
const newObject = { ...oldObject, newProperty: value };
const merged = { ...defaults, ...options };

// INCORRECT
const newArray = oldArray.concat( [ newItem ] );
const newObject = Object.assign( {}, oldObject, { newProperty: value } );
```

### Computed Property Names

Use computed property names when needed.

```javascript
// CORRECT
const key = 'dynamicKey';
const obj = {
	[ key ]: value,
	[ `prefix_${ key }` ]: otherValue,
};
```

---

## 4. Modules

### ES Modules Only

Use ES modules (import/export) for all new code.

```javascript
// CORRECT
import { getState, setState } from './shared';
import defaultExport from './module';
export const myFunction = () => {};
export default mainFunction;

// INCORRECT
const module = require( './module' );
module.exports = myFunction;
```

**Exception:** `js/src/admin/admin.js` uses `require()` syntax. This is mandatory for that file due to its build configuration. All other files should use ES modules.

### Named Exports Preferred

Prefer named exports over default exports for better refactoring.

```javascript
// CORRECT
export const processData = ( data ) => {};
export const validateData = ( data ) => {};

// Use
import { processData, validateData } from './utils';
```

### Single Import Per Module

Import from a module only once per file.

```javascript
// CORRECT
import { foo, bar, baz } from './utils';

// INCORRECT
import { foo } from './utils';
import { bar } from './utils';
```

### No Wildcard Imports

Avoid wildcard imports in production code.

```javascript
// CORRECT
import { specificFunction } from './utils';

// INCORRECT
import * as utils from './utils';
```

---

## 5. DOM Manipulation

### Query Selectors

Use native query selectors.

```javascript
// Single element
const element = document.querySelector( '.my-class' );
const byId = document.getElementById( 'my-id' );

// Multiple elements
const elements = document.querySelectorAll( '.my-class' );

// Scoped query
const child = parent.querySelector( '.child' );
```

### Element Creation with frmDom

Use `frmDom` helpers for creating DOM elements. These are available globally via `window.frmDom`.

```javascript
const { div, span, tag, a, img, svg } = frmDom;

// Create a div with class and text
const myDiv = div( {
	className: 'my-class',
	text: 'Hello'
} );

// Create a div with children
const container = div( {
	className: 'container',
	children: [
		span( { text: 'Label:' } ),
		span( { className: 'value', text: 'Content' } )
	]
} );

// Create any element with tag()
const input = tag( 'input', {
	id: 'my-input',
	className: 'frm-input'
} );
input.type = 'text';
input.setAttribute( 'name', 'field_name' );

// Create anchor with href
const link = a( {
	href: 'https://example.com',
	text: 'Click here',
	target: '_blank'
} );

// Create image
const image = img( {
	src: '/path/to/image.png',
	alt: 'Description'
} );

// Create SVG icon
const icon = svg( { href: '#frm_close_icon' } );

// Append to container
container.appendChild( myDiv );
```

### Event Handling with frmDom.util

Use `frmDom.util` helpers for common event patterns.

```javascript
const { util } = frmDom;

// Click with preventDefault
util.onClickPreventDefault( element, ( event ) => {
	// Handler logic
} );

// Event delegation (like jQuery's document.on)
util.documentOn( 'click', '.my-selector', ( event ) => {
	// Handler logic: 'this' refers to matched element
} );

// Debounced function
const debouncedSearch = util.debounce( handleSearch, 300 );
input.addEventListener( 'input', debouncedSearch );
```

### Native Event Handling

For cases not covered by `frmDom.util`, use native APIs:

```javascript
// Add listener
element.addEventListener( 'click', handleClick );

// Remove listener
element.removeEventListener( 'click', handleClick );

// Custom events
element.dispatchEvent( new CustomEvent( 'custom-event', { detail: data } ) );
```

### Class Manipulation

```javascript
element.classList.add( 'active' );
element.classList.remove( 'active' );
element.classList.toggle( 'active' );
element.classList.contains( 'active' );
element.classList.replace( 'old', 'new' );
```

### DOM Sanitization

Use `frmDom.cleanNode` for sanitizing DOM nodes.

```javascript
// CORRECT: Use existing Formidable helper
frmDom.cleanNode( element );

// INCORRECT: Do not add new dependencies
import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize( userData );
```

---

## 6. Async Operations

### AJAX with frmDom.ajax

Use `frmDom.ajax` helpers for WordPress AJAX requests. These automatically handle nonces.

```javascript
const { ajax } = frmDom;

// GET request: action becomes 'frm_' + action
try {
	const data = await ajax.doJsonFetch( 'get_template&template_id=123' );
	// data is the response from WordPress
} catch ( error ) {
	console.error( 'Request failed:', error );
}

// POST request with FormData
const formData = new FormData();
formData.append( 'template_id', templateId );
formData.append( 'name', templateName );

try {
	const data = await ajax.doJsonPost( 'save_template', formData );
	// data is the response from WordPress
} catch ( error ) {
	console.error( 'Request failed:', error );
}

// POST with abort signal
const controller = new AbortController();
try {
	const data = await ajax.doJsonPost( 'search', formData, {
		signal: controller.signal
	} );
} catch ( error ) {
	if ( error.name === 'AbortError' ) {
		// Request was cancelled
	}
}
// To cancel: controller.abort();
```

### Native Fetch API

For non-WordPress AJAX or external APIs, use native Fetch:

```javascript
// GET request
const response = await fetch( '/api/data' );
const data = await response.json();

// POST request
const response = await fetch( '/api/submit', {
	method: 'POST',
	headers: {
		'Content-Type': 'application/json',
	},
	body: JSON.stringify( payload ),
} );
```

### Async/Await

Prefer async/await over Promise chains.

```javascript
// CORRECT
async function loadData() {
	try {
		const data = await frmDom.ajax.doJsonFetch( 'get_data' );
		return processData( data );
	} catch ( error ) {
		handleError( error );
		return null;
	}
}

// INCORRECT: Promise chain for simple operations
function loadData() {
	return fetch( url )
		.then( ( response ) => response.json() )
		.then( ( data ) => processData( data ) )
		.catch( ( error ) => handleError( error ) );
}
```

### Parallel Async Operations

Use Promise.all for independent parallel operations.

```javascript
// CORRECT: Parallel execution
const [ templates, categories ] = await Promise.all( [
	frmDom.ajax.doJsonFetch( 'get_templates' ),
	frmDom.ajax.doJsonFetch( 'get_categories' ),
] );

// INCORRECT: Sequential when parallel is possible
const templates = await frmDom.ajax.doJsonFetch( 'get_templates' );
const categories = await frmDom.ajax.doJsonFetch( 'get_categories' );
```

---

## 7. Error Handling

### Narrow Try-Catch Scope

Wrap only operations that can actually fail. Avoid wrapping logic that does not need error handling.

```javascript
// CORRECT: Narrow scope around async operation
async function fetchData() {
	let response;
	try {
		response = await fetch( url );
	} catch ( error ) {
		console.error( 'Fetch failed:', error.message );
		return null;
	}

	// Parsing logic outside try-catch (let errors surface if data is malformed)
	return response.json();
}

// INCORRECT: Overly broad try-catch
async function fetchData() {
	try {
		const response = await fetch( url );
		const data = await response.json();
		const processed = transformData( data ); // This doesn't need try-catch
		updateUI( processed ); // This doesn't need try-catch
		return processed;
	} catch ( error ) {
		// Catches everything, including non-network errors
		console.error( error );
		return null;
	}
}
```

### When to Use Try-Catch

- **Use for:** Network requests, file operations, JSON parsing, external API calls
- **Avoid for:** Simple logic, DOM operations, array methods, synchronous code that should fail loudly

### Meaningful Error Messages

Provide context in error messages.

```javascript
// CORRECT
throw new Error( `Failed to load template: ${ templateId }` );

// INCORRECT
throw new Error( 'Error' );
```

---

## 8. State Management

### Encapsulated State

Use closures or modules for state management.

```javascript
// CORRECT: Module pattern from codebase
import {
	getState,
	getSingleState,
	setState,
	setSingleState,
} from 'core/page-skeleton';

// Initialize state
setState( {
	currentView: 'list',
	selectedItem: null,
	isLoading: false,
} );

// Update state
setSingleState( 'isLoading', true );

// Read state
const { currentView, selectedItem } = getState();
```

### Immutable Updates

Never mutate state directly.

```javascript
// CORRECT
const newState = {
	...state,
	items: [ ...state.items, newItem ],
};

// INCORRECT
state.items.push( newItem );
```

---

## 9. No Inline Event Handlers

Do **not** use inline event handlers (`onclick`, `onkeydown`, etc.) in HTML. Keep all JS logic in JS files using `addEventListener`.

```html
<!-- INCORRECT: Inline handlers -->
<div
	role="button"
	tabindex="0"
	onclick="handleClick()"
	onkeydown="handleKeydown( event )"
>
	Custom Button
</div>

<!-- CORRECT: No inline handlers, attach via JS -->
<div
	role="button"
	tabindex="0"
	class="js-custom-button"
>
	Custom Button
</div>
```

```javascript
// Attach events in JS files
const button = document.querySelector( '.js-custom-button' );
button.addEventListener( 'click', handleClick );
button.addEventListener( 'keydown', ( event ) => {
	if ( event.key === 'Enter' || event.key === ' ' ) {
		event.preventDefault();
		handleClick();
	}
} );
```

---

## 10. Anti-Patterns to Avoid

### Global Variable Pollution

```javascript
// INCORRECT
myGlobalVar = 'value';
window.myApp = {};

// CORRECT: Use modules
export const myValue = 'value';
```

**Note:** Some `window` variables exist in `js/src/admin/admin.js` and other legacy files as architectural decisions. New code and updates should avoid this practice and use modules instead.

### Callback Hell

```javascript
// INCORRECT
getData( ( data ) => {
	processData( data, ( result ) => {
		saveData( result, ( response ) => {
			// Deeply nested
		} );
	} );
} );

// CORRECT
const data = await getData();
const result = await processData( data );
const response = await saveData( result );
```

### Modifying Built-in Prototypes

```javascript
// NEVER DO THIS
Array.prototype.customMethod = function () {};
String.prototype.myHelper = function () {};
```

### Using eval() or Function()

```javascript
// NEVER DO THIS
eval( userInput );
new Function( userInput )();
```

### Magic Numbers/Strings

```javascript
// INCORRECT
if ( 3 === status ) {
}
element.style.width = '768px';

// CORRECT
const STATUS_COMPLETE = 3;
const TABLET_BREAKPOINT = '768px';

if ( STATUS_COMPLETE === status ) {
}
element.style.width = TABLET_BREAKPOINT;
```

### Excessive DOM Queries

```javascript
// INCORRECT
document.querySelector( '.item' ).classList.add( 'active' );
document.querySelector( '.item' ).textContent = 'Updated';
document.querySelector( '.item' ).dataset.id = '123';

// CORRECT: Cache the reference
const item = document.querySelector( '.item' );
item.classList.add( 'active' );
item.textContent = 'Updated';
item.dataset.id = '123';
```

### DOM Manipulation in Loops

```javascript
// INCORRECT: Causes reflow on each iteration
items.forEach( ( item ) => {
	container.appendChild( createItem( item ) );
} );

// CORRECT: Use DocumentFragment
const fragment = document.createDocumentFragment();
items.forEach( ( item ) => {
	fragment.appendChild( createItem( item ) );
} );
container.appendChild( fragment );
```

---

## 11. Performance Patterns

### Cache Function Results

```javascript
// Module-level cache
const cache = new Map();

function expensiveOperation( key ) {
	if ( cache.has( key ) ) {
		return cache.get( key );
	}

	const result = /* expensive computation */;
	cache.set( key, result );
	return result;
}
```

### Use Set/Map for Lookups

```javascript
// CORRECT: O(1) lookup
const selectedIds = new Set( selectedItems.map( ( item ) => item.id ) );
const isSelected = ( id ) => selectedIds.has( id );

// INCORRECT: O(n) lookup
const isSelected = ( id ) => selectedItems.some( ( item ) => item.id === id );
```

---

## 12. Optional Chaining Best Practices

Use optional chaining (`?.`) judiciously. Overuse can hide bugs and make code harder to debug.

### When to Use

```javascript
// CORRECT: Accessing deeply nested optional properties
const userName = response?.data?.user?.name;

// CORRECT: Calling methods that may not exist
callback?.();

// CORRECT: Accessing properties on potentially null DOM elements
const value = document.getElementById( 'my-input' )?.value;
```

### When NOT to Use

```javascript
// INCORRECT: NodeList.forEach already handles empty lists
const elements = document.querySelectorAll( '.item' );
elements?.forEach( handleItem ); // Unnecessary - forEach on empty NodeList is safe
elements.forEach( handleItem );  // CORRECT

// INCORRECT: Excessive chaining when explicit checks are clearer
if ( user?.profile?.settings?.notifications?.email ) {
	// Hard to debug which part is null
}

// CORRECT: Explicit checks for complex logic
if ( ! user || ! user.profile ) {
	return handleMissingUser();
}
const { settings } = user.profile;
if ( settings?.notifications?.email ) {
	// Clear where the optional access starts
}

// INCORRECT: Using ?. on values you control/expect to exist
const form = document.getElementById( 'main-form' );
form?.submit(); // If form should exist, let it error so you know something is wrong

// CORRECT: Let expected values error if missing
const form = document.getElementById( 'main-form' );
form.submit(); // Errors if form doesn't exist (which is a bug to fix)
```

### Guidelines

- **Do not use** `?.` on `querySelectorAll` results. Empty `NodeList` is safe to iterate.
- **Do not use** `?.` to silence errors on values that should always exist.
- **Use** `?.` for external data, API responses, and optional configurations.
- **Prefer** explicit `if` checks when null handling requires different logic paths.

---

## Tooling

```bash
# Install ESLint with WordPress config
npm install --save-dev @wordpress/eslint-plugin

# .eslintrc.json
{
    "extends": [ "plugin:@wordpress/eslint-plugin/recommended" ],
    "rules": {
        "no-var": "error",
        "prefer-const": "error",
        "no-unused-vars": "error"
    }
}
```
