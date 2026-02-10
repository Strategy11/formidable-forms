---
trigger: glob
globs: ["**/*.js", "**/*.jsx", "**/*.mjs"]
description: WordPress JavaScript coding standards. Auto-applies when working with JS files.
---

# WordPress JavaScript Coding Standards

Based on WordPress Core Official Standards. Apply when maintaining, generating, or refactoring JavaScript code.

**Reference:** [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

---

## 1. Spacing

### General Rules

- Use tabs for indentation
- No whitespace at end of line or on blank lines
- Lines should be 80-100 characters maximum
- Always use braces for `if`, `else`, `for`, `while`, `try`
- Add space after `!` negation operator
- Include new line at end of each file

### Object Declarations

Use multiline format for clarity.

```javascript
var obj = {
    ready: 9,
    when: 4,
    'you are': 15,
};
```

For single property, inline is acceptable.

```javascript
var obj = { ready: 9 };
```

### Array Declarations

```javascript
var arr = [
    'one',
    'two',
    'three',
];
```

### Function Calls

Include space inside parentheses.

```javascript
foo( arg1, arg2 );
```

No space for empty parentheses.

```javascript
foo();
```

### Control Structures

```javascript
if ( condition ) {
    doSomething( 'with a string' );
} else if ( otherCondition ) {
    otherThing( {
        key: value,
    } );
} else {
    somethingElse( true );
}

while ( ! condition ) {
    iterating++;
}

for ( i = 0; i < 100; i++ ) {
    object[ array[ i ] ] = someFn( i );
}
```

### Operators

Include spaces around operators.

```javascript
var x = a + b;
var y = a && b;
var z = a ? b : c;
```

---

## 2. Indentation and Line Breaks

### Tab Indentation

Use tabs for all indentation, including inside closures.

```javascript
( function( $ ) {
    function doSomething() {
        // Expressions indented with tabs
    }
} )( jQuery );
```

### Blocks

Opening brace on same line as the statement. Closing brace on its own line after the last statement.

```javascript
if ( condition ) {
    doAction();
}
```

### Multi-line Statements

When a statement is too long to fit on one line, line breaks must occur after an operator.

```javascript
var html = '<p>The sum of ' + a + ' and ' + b + ' plus ' + c +
    ' is ' + ( a + b + c ) + '</p>';
```

### Chained Method Calls

Use one call per line for chains that are hard to read on one line.

```javascript
elements
    .addClass( 'foo' )
    .children()
    .html( 'hello' )
    .end()
    .appendTo( 'body' );
```

---

## 3. Variables and Naming

### Variable Declarations

Use `const` for values that do not change. Use `let` for values that change. Avoid `var` in modern code.

```javascript
const MAX_COUNT = 100;
let currentCount = 0;
```

Declare each variable on its own line.

```javascript
let a = 1;
let b = 2;
```

### Naming Conventions

Use camelCase with lowercase first letter for variables and functions.

```javascript
var someVariable = 'value';
function myFunction() {}
```

### Class Definitions

Use UpperCamelCase (PascalCase) for class names.

```javascript
class MyClass {
    constructor() {}
}
```

### Constants

Use SCREAMING_SNAKE_CASE for true constants.

```javascript
const MAX_ITEMS = 100;
const API_ENDPOINT = '/api/v1';
```

### Private Properties

Prefix private properties with underscore.

```javascript
this._privateProperty = 'value';
```

### Globals

Avoid global variables. If unavoidable, set via `window`.

```javascript
window.myGlobal = 'value';
```

### jQuery Pattern

Use IIFE to protect jQuery dollar sign.

```javascript
( function( $ ) {
    // Use $ safely here
} )( jQuery );
```

---

## 4. Equality and Type Checks

### Strict Equality

Always use strict equality operators `===` and `!==`.

```javascript
// Correct
if ( name === 'John' ) {}
if ( count !== 0 ) {}

// Incorrect
if ( name == 'John' ) {}
if ( count != 0 ) {}
```

### Type Checks

| Type | Check |
|------|-------|
| String | `typeof object === 'string'` |
| Number | `typeof object === 'number'` |
| Boolean | `typeof object === 'boolean'` |
| Object | `typeof object === 'object'` |
| Function | `typeof object === 'function'` |
| null | `object === null` |
| undefined | `typeof variable === 'undefined'` |
| Property exists | `object.hasOwnProperty( prop )` |
| Array | `Array.isArray( object )` |

---

## 5. Syntax Rules

### Semicolons

Always use semicolons. Never rely on Automatic Semicolon Insertion (ASI).

```javascript
var foo = 'bar';
```

### Strings

Use single quotes for strings.

```javascript
var myStr = 'strings should use single quotes';
```

For HTML inside JavaScript, use single quotes for the JavaScript string and double quotes for HTML attributes.

```javascript
var html = '<a href="' + url + '">Link</a>';
```

### Template Literals

Use template literals for string interpolation in ES6+.

```javascript
const message = `Hello, ${name}!`;
```

### Switch Statements

Use `break` for each case except when explicitly falling through. Indent `case` one tab from `switch`.

```javascript
switch ( event.keyCode ) {
    case $.ui.keyCode.ENTER:
    case $.ui.keyCode.SPACE:
        executeFunction();
        break;

    case $.ui.keyCode.ESCAPE:
        closeModal();
        break;

    default:
        break;
}
```

---

## 6. Best Practices

### Comments

Place comments on line before the code. Precede with blank line. Use JSDoc for documentation.

```javascript
/**
 * Function description.
 *
 * @param {string} param1 Description of parameter.
 * @return {boolean} Description of return value.
 */
function myFunction( param1 ) {
    // Single-line comment
    return true;
}
```

### Arrays and Objects

Create arrays and objects using literal syntax.

```javascript
// Correct
var arr = [];
var obj = {};

// Incorrect
var arr = new Array();
var obj = new Object();
```

### Iteration

For collections, use appropriate iteration methods.

```javascript
// jQuery
$.each( collection, function( index, item ) {
    // code
} );

// Native
collection.forEach( function( item, index ) {
    // code
} );

// ES6+
for ( const item of collection ) {
    // code
}
```

### Avoid eval()

Never use `eval()`.

### Avoid with Statement

Never use `with` statement.

### Code Refactoring

Do not refactor working code just for style. Only refactor if you are already modifying that code.

---

## 7. Functions

### Function Declarations

```javascript
function myFunction( arg1, arg2 ) {
    return arg1 + arg2;
}
```

### Function Expressions

```javascript
var myFunction = function( arg1, arg2 ) {
    return arg1 + arg2;
};
```

### Arrow Functions (ES6+)

```javascript
const myFunction = ( arg1, arg2 ) => {
    return arg1 + arg2;
};

// Short form for single expression
const double = ( n ) => n * 2;
```

### Default Parameters

```javascript
function greet( name = 'World' ) {
    return 'Hello, ' + name;
}
```

---

## 8. Classes (ES6+)

```javascript
class Animal {
    constructor( name ) {
        this.name = name;
    }

    speak() {
        console.log( this.name + ' makes a sound.' );
    }
}

class Dog extends Animal {
    constructor( name ) {
        super( name );
    }

    speak() {
        console.log( this.name + ' barks.' );
    }
}
```

---

## 9. Modules (ES6+)

### Importing

```javascript
import { Component } from 'react';
import * as utils from './utils';
import defaultExport from './module';
```

### Exporting

```javascript
export function myFunction() {}
export const myConstant = 'value';
export default MyClass;
```

---

## 10. Promises and Async

### Promises

```javascript
fetchData()
    .then( function( data ) {
        return processData( data );
    } )
    .then( function( result ) {
        displayResult( result );
    } )
    .catch( function( error ) {
        handleError( error );
    } );
```

### Async/Await (ES2017+)

```javascript
async function getData() {
    try {
        const data = await fetchData();
        const result = await processData( data );
        return result;
    } catch ( error ) {
        handleError( error );
    }
}
```

---

## JSHint Configuration

```json
{
    "boss": true,
    "curly": true,
    "eqeqeq": true,
    "eqnull": true,
    "es3": true,
    "expr": true,
    "immed": true,
    "noarg": true,
    "nonbsp": true,
    "onevar": true,
    "quotmark": "single",
    "trailing": true,
    "undef": true,
    "unused": true,
    "browser": true,
    "globals": {
        "_": false,
        "Backbone": false,
        "jQuery": false,
        "JSON": false,
        "wp": false
    }
}
```

---

## ESLint Configuration

For modern projects, use ESLint with WordPress config.

```bash
npm install --save-dev @wordpress/eslint-plugin
```

```json
{
    "extends": [ "plugin:@wordpress/eslint-plugin/recommended" ]
}
```
