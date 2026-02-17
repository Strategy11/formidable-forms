---
trigger: glob
globs: ["**/*.js", "**/*.jsx", "**/*.mjs"]
description: WordPress JavaScript coding standards based on WordPress Core Official Standards. Auto-applies when working with JS files.
---

# WordPress JavaScript Coding Standards

Based on WordPress Core Official Standards.

**Reference:** [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

---

## Code Refactoring

While coding standards are important, refactoring older `.js` files simply to conform to the standards is not an urgent issue. "Whitespace-only" patches for older files are strongly discouraged.

---

## 1. Spacing

Use spaces liberally throughout your code. "When in doubt, space it out."

### General Rules

- Indentation with tabs
- No whitespace at the end of line or on blank lines
- Lines should usually be no longer than 80 characters, and should not exceed 100
- `if`/`else`/`for`/`while`/`try` blocks should always use braces, and always go on multiple lines
- Unary special-character operators (e.g., `++`, `--`) must not have space next to their operand
- Any `,` and `;` must not have preceding space
- Any `;` used as a statement terminator must be at the end of the line
- Any `:` after a property name in an object definition must not have preceding space
- The `?` and `:` in a ternary conditional must have space on both sides
- No filler spaces in empty constructs (e.g., `{}`, `[]`, `fn()`)
- There should be a new line at the end of each file
- Any `!` negation operator should have a following space
- All function bodies are indented by one tab, even if the entire file is wrapped in a closure
- Spaces may align code within documentation blocks or within a line, but only tabs should be used at the start of a line

### Object Declarations

Object declarations can be made on a single line if they are short. When an object declaration is too long to fit on one line, there must be one property per line and each line ended by a comma.

```javascript
// Preferred
const obj = {
	ready: 9,
	when: 4,
	'you are': 15,
};

// Acceptable for small objects
const obj = { ready: 9, when: 4, 'you are': 15 };
```

### Arrays and Function Calls

Always include extra spaces around elements and arguments:

```javascript
array = [ a, b ];

foo( arg );
foo( 'string', object );
foo( options, object[ property ] );
foo( node, 'property', 2 );

prop = object[ 'default' ];
firstArrayElement = arr[ 0 ];
```

### Examples of Good Spacing

```javascript
let i;

if ( condition ) {
	doSomething( 'with a string' );
} else if ( otherCondition ) {
	otherThing( {
		key: value,
		otherKey: otherValue,
	} );
} else {
	somethingElse( true );
}

// WordPress prefers a space after the ! negation operator.
while ( ! condition ) {
	iterating++;
}

for ( i = 0; i < 100; i++ ) {
	object[ array[ i ] ] = someFn( i );
}

try {
	// Expressions
} catch ( e ) {
	// Expressions
}
```

---

## 2. Semicolons

Use them. Never rely on Automatic Semicolon Insertion (ASI).

---

## 3. Indentation and Line Breaks

Tabs should be used for indentation. For legacy code wrapped in an IIFE (immediately invoked function expression), the contents should be indented by one tab:

```javascript
// Legacy IIFE pattern (use ES6 modules for new code)
( function () {
	// Expressions indented

	function doSomething() {
		// Expressions indented
	}
} )();
```

**Note:** New code should use ES6 modules instead of IIFEs.

### Blocks and Curly Braces

`if`, `else`, `for`, `while`, and `try` blocks should always use braces, and always go on multiple lines. The opening brace should be on the same line as the function definition, the conditional, or the loop. The closing brace should be on the line directly following the last statement of the block.

```javascript
let a, b, c;

if ( myFunction() ) {
	// Expressions
} else if ( ( a && b ) || c ) {
	// Expressions
} else {
	// Expressions
}
```

### Multi-line Statements

When a statement is too long to fit on one line, line breaks must occur after an operator.

```javascript
// Bad
const html = '<p>The sum of ' + a + ' and ' + b + ' plus ' + c
	+ ' is ' + ( a + b + c ) + '</p>';

// Good
const html = '<p>The sum of ' + a + ' and ' + b + ' plus ' + c +
	' is ' + ( a + b + c ) + '</p>';
```

Lines should be broken into logical groups if it improves readability:

```javascript
// Acceptable
const baz = ( true === conditionalStatement() ) ? 'thing 1' : 'thing 2';

// Better
const baz = firstCondition( foo ) && secondCondition( bar ) ?
	qux( foo, bar ) :
	foo;
```

When a conditional is too long to fit on one line, each operand of a logical operator in the boolean expression must appear on its own line:

```javascript
if (
	firstCondition() &&
	secondCondition() &&
	thirdCondition()
) {
	doStuff();
}
```

### Chained Method Calls

When a chain of method calls is too long to fit on one line, there must be one call per line, with the first call on a separate line from the object the methods are called on:

```javascript
elements
	.addClass( 'foo' )
	.children()
		.html( 'hello' )
	.end()
	.appendTo( 'body' );
```

---

## 4. Assignments and Globals

### Declaring Variables with const and let

For code written using ES2015 or newer, `const` and `let` should always be used in place of `var`. A declaration should use `const` unless its value will be reassigned, in which case `let` is appropriate.

Unlike `var`, it is not necessary to declare all variables at the top of a function. Instead, they are to be declared at the point at which they are first used.

### Globals

All globals used within a file should be documented at the top of that file. Multiple globals can be comma-separated.

```javascript
/* global passwordStrength:true */
```

The "true" after `passwordStrength` means that this global is being defined within this file. If you are accessing a global which is defined elsewhere, omit `:true` to designate the global as read-only.

### Common Libraries

The global `wp` object is registered as an allowed global.

**Legacy Note:** Backbone, jQuery, and Underscore were common in older WordPress code but should be avoided in new code. Use native JavaScript and ES6+ features instead.

Files which add to, or modify, the `wp` object must safely access the global:

```javascript
// At the top of the file, set "wp" to its existing value (if present)
window.wp = window.wp || {};
```

---

## 5. Naming Conventions

Variable and function names should be full words, using camel case with a lowercase first letter. Names should be descriptive, but not excessively so. Exceptions are allowed for iterators, such as the use of `i` to represent the index in a loop.

| Type        | Convention           | Example                      |
| ----------- | -------------------- | ---------------------------- |
| Variables   | camelCase            | `currentUser`, `itemCount`   |
| Functions   | camelCase            | `getUserData`, `handleClick` |
| Classes     | PascalCase           | `UserManager`, `Earth`       |
| Constants   | SCREAMING_SNAKE_CASE | `MAX_ITEMS`, `API_URL`       |

### Abbreviations and Acronyms

Acronyms must be written with each of its composing letters capitalized. All other abbreviations must be written as camel case.

```javascript
// "Id" is an abbreviation of "Identifier":
const userId = 1;

// "DOM" is an acronym of "Document Object Model":
const currentDOMDocument = window.document;

// Acronyms at the start follow camelcase rules
const domDocument = window.document;
class DOMDocument {}
class IdCollection {}
```

### Class Definitions

Constructors intended for use with `new` should have a capital first letter (UpperCamelCase). A `class` definition must use the UpperCamelCase convention.

```javascript
class Earth {
	static addHuman( human ) {
		Earth.humans.push( human );
	}

	static getHumans() {
		return Earth.humans;
	}
}

Earth.humans = [];
```

All `@wordpress/element` Components, including stateless function components, should be named using Class Definition naming rules.

### Constants

An exception to camel case is made for constant values which are never intended to be reassigned or mutated. Such variables must use the SCREAMING_SNAKE_CASE convention.

---

## 6. Comments

Comments come before the code to which they refer, and should always be preceded by a blank line. Capitalize the first letter of the comment, and include a period at the end when writing full sentences. There must be a single space between the comment token (`//`) and the comment text.

```javascript
someStatement();

// Explanation of something complex on the next line
document.querySelector( 'p' ).doSomething();

// This is a comment that is long enough to warrant being stretched
// over the span of multiple lines.
```

JSDoc comments should use the `/**` multi-line comment opening.

Inline comments are allowed as an exception when used to annotate special arguments:

```javascript
function foo( types, selector, data, fn, /* INTERNAL */ one ) {
	// Do stuff
}
```

---

## 7. Equality

Strict equality checks (`===`) must be used in favor of abstract equality checks (`==`).

---

## 8. Type Checks

These are the preferred ways of checking the type of an object:

| Type      | Check                                        |
| --------- | -------------------------------------------- |
| String    | `typeof object === 'string'`                 |
| Number    | `typeof object === 'number'`                 |
| Boolean   | `typeof object === 'boolean'`                |
| Object    | `typeof object === 'object'`                 |
| Function  | `typeof object === 'function'`               |
| Array     | `Array.isArray( object )`                    |
| Element   | `object.nodeType`                            |
| null      | `object === null`                            |
| undefined | `typeof variable === 'undefined'` (globals)  |
| undefined | `variable === undefined` (local)             |

---

## 9. Strings

Use single-quotes for string literals:

```javascript
const myStr = 'strings should be contained in single quotes';
```

When a string contains single quotes, they need to be escaped with a backslash (`\`):

```javascript
// Escape single quotes within strings:
'Note the backslash before the \'single quotes\'';
```

---

## 10. Switch Statements

The usage of `switch` statements is generally discouraged, but can be useful when there are a large number of cases.

When using `switch` statements:

- Use a `break` for each case other than `default`. When allowing statements to "fall through," note that explicitly.
- Indent `case` statements one tab within the `switch`.

```javascript
switch ( event.key ) {

	// ENTER and SPACE both trigger x()
	case 'Enter':
	case ' ':
		x();
		break;

	case 'Escape':
		y();
		break;

	default:
		z();
}
```

It is not recommended to return a value from within a switch statement: use the `case` blocks to set values, then `return` those values at the end.

---

## 11. Best Practices

### Arrays

Creating arrays in JavaScript should be done using the shorthand `[]` constructor rather than the `new Array()` notation.

```javascript
const myArray = [];
const myArray = [ 1, 'WordPress', 2, 'Blog' ];
```

### Objects

Object literal notation, `{}`, is both the most performant, and also the easiest to read.

```javascript
const myObj = {};
```

Object literal notation should be used unless the object requires a specific prototype, in which case the object should be created by calling a constructor function with `new`.

Object properties should be accessed via dot notation, unless the key is a variable or a string that would not be a valid identifier:

```javascript
prop = object.propertyName;
prop = object[ variableKey ];
prop = object[ 'key-with-hyphens' ];
```

### Iteration

When iterating over a large collection using a `for` loop, it is recommended to store the loop's max value as a variable rather than re-computing the maximum every time:

```javascript
// Good & Efficient
const max = getItemCount();

// getItemCount() gets called once
for ( let i = 0; i < max; i++ ) {
	// Do stuff
}

// Bad & Potentially Inefficient:
// getItemCount() gets called every time
for ( let i = 0; i < getItemCount(); i++ ) {
	// Do stuff
}
```

---

## 12. WordPress Integration

### Using wp.hooks

```javascript
// Actions
wp.hooks.doAction( 'myPlugin.beforeInit', { data } );
wp.hooks.addAction( 'myPlugin.afterSave', 'myPlugin', handleAfterSave );

// Filters
const value = wp.hooks.applyFilters( 'myPlugin.filterValue', defaultValue );
wp.hooks.addFilter( 'myPlugin.filterValue', 'myPlugin', modifyValue );
```

### Using @wordpress packages

```javascript
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

domReady( () => {
	initializeModule();
} );
```

---

## JSHint

JSHint is an automated code quality tool, designed to catch errors in your JavaScript code.

### JSHint Settings

The configuration options used for JSHint are stored within a `.jshintrc` file.

### JSHint Overrides: Ignore Blocks

To exclude a specific file region from being processed by JSHint, enclose it in JSHint directive comments:

```javascript
/* jshint ignore:start */
if ( typeof thirdPartyLibrary === 'undefined' ) {
	// Third-party code
}
/* jshint ignore:end */
```
