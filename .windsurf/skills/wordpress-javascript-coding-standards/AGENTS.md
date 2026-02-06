# WordPress JavaScript Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring JavaScript code in the WordPress ecosystem.

---

## Abstract

JavaScript has become a critical component in developing WordPress-based applications (themes and plugins) as well as WordPress core. These standards ensure consistency, readability, and maintainability.

---

## Table of Contents

1. [Spacing](#1-spacing) — **HIGH**
2. [Indentation and Line Breaks](#2-indentation-and-line-breaks) — **HIGH**
3. [Variables and Naming](#3-variables-and-naming) — **MEDIUM**
4. [Equality and Type Checks](#4-equality-and-type-checks) — **MEDIUM**
5. [Syntax Rules](#5-syntax-rules) — **MEDIUM**
6. [Best Practices](#6-best-practices) — **LOW**

---

## 1. Spacing

**Impact: HIGH**

Use spaces liberally for improved readability. Minification handles optimization.

### 1.1 General Rules

- Indentation with tabs
- No whitespace at end of line or on blank lines
- Lines should usually be no longer than 80 characters, max 100
- `if`/`else`/`for`/`while`/`try` blocks always use braces and multiple lines
- Unary operators (`++`, `--`) must not have space next to operand
- `,` and `;` must not have preceding space
- `:` after property name must not have preceding space
- `?` and `:` in ternary must have space on both sides
- No filler spaces in empty constructs (`{}`, `[]`, `fn()`)
- New line at end of each file
- `!` negation operator should have following space

### 1.2 Object Declarations

**Incorrect:**

```javascript
var obj = { ready: 9, when: 4, "you are": 15 };
```

**Correct (multiline preferred):**

```javascript
var obj = {
  ready: 9,
  when: 4,
  "you are": 15,
};
```

**Acceptable for small objects:**

```javascript
var obj = { ready: 9, when: 4, "you are": 15 };
```

### 1.3 Arrays and Function Calls

Always include extra spaces around elements and arguments.

**Correct:**

```javascript
array = [a, b];

foo(arg);
foo("string", object);
foo(options, object[property]);
foo(node, "property", 2);

prop = object["default"];
firstArrayElement = arr[0];
```

### 1.4 Control Structures

**Correct:**

```javascript
var i;

if (condition) {
  doSomething("with a string");
} else if (otherCondition) {
  otherThing({
    key: value,
    otherKey: otherValue,
  });
} else {
  somethingElse(true);
}

// Space after ! negation (differs from jQuery)
while (!condition) {
  iterating++;
}

for (i = 0; i < 100; i++) {
  object[array[i]] = someFn(i);
  $(".container").val(array[i]);
}

try {
  // Expressions
} catch (e) {
  // Expressions
}
```

---

## 2. Indentation and Line Breaks

**Impact: HIGH**

### 2.1 Tab Indentation

Use tabs for indentation, even inside closures.

**Correct:**

```javascript
(function ($) {
  // Expressions indented

  function doSomething() {
    // Expressions indented
  }
})(jQuery);
```

### 2.2 Blocks and Curly Braces

Opening brace on same line. Closing brace on line after last statement.

**Correct:**

```javascript
var a, b, c;

if (myFunction()) {
  // Expressions
} else if ((a && b) || c) {
  // Expressions
} else {
  // Expressions
}
```

### 2.3 Multi-line Statements

Line breaks must occur after an operator.

**Incorrect:**

```javascript
var html =
  "<p>The sum of " +
  a +
  " and " +
  b +
  " plus " +
  c +
  " is " +
  (a + b + c) +
  "</p>";
```

**Correct:**

```javascript
var html =
  "<p>The sum of " +
  a +
  " and " +
  b +
  " plus " +
  c +
  " is " +
  (a + b + c) +
  "</p>";
```

**Ternary on multiple lines:**

```javascript
var baz = firstCondition(foo) && secondCondition(bar) ? qux(foo, bar) : foo;
```

**Long conditionals:**

```javascript
if (firstCondition() && secondCondition() && thirdCondition()) {
  doStuff();
}
```

### 2.4 Chained Method Calls

One call per line. Extra indent when context changes.

**Correct:**

```javascript
elements.addClass("foo").children().html("hello").end().appendTo("body");
```

---

## 3. Variables and Naming

**Impact: MEDIUM**

### 3.1 Variable Declarations

Use `const` and `let` (ES6+). Avoid `var` in modern code.

**Correct:**

```javascript
const myName = "WordPress";
let counter = 0;
```

**Legacy (when var is required):**

```javascript
var myName = "WordPress";
```

### 3.2 Naming Conventions

Use camelCase with lowercase first letter. This differs from WordPress PHP standards.

**Incorrect:**

```javascript
var some_variable = "value";
var SomeVariable = "value";
```

**Correct:**

```javascript
var someVariable = "value";

function myFunction() {}

// Iterators allowed as single letters
for (var i = 0; i < 10; i++) {}
```

### 3.3 Abbreviations and Acronyms

Treat as words in camelCase.

**Correct:**

```javascript
var defined;
var htmlContent;
var jsonData;
var xmlParser;
```

### 3.4 Class Definitions

Use UpperCamelCase.

**Correct:**

```javascript
class MyClass {
  constructor() {}
}
```

### 3.5 Constants

Use SCREAMING_SNAKE_CASE.

**Correct:**

```javascript
const MAX_ITEMS = 100;
const API_URL = "https://api.example.com";
```

### 3.6 Globals

Avoid globals. If unavoidable, set them explicitly via `window`.

**Correct:**

```javascript
window.myGlobal = "value";
```

**Document globals at top of file:**

```javascript
/* global passwordStrength:true */
```

- `:true` means the global is being defined in this file
- Omit `:true` for read-only globals defined elsewhere

### 3.7 Common Libraries

Backbone, jQuery, Underscore, and `wp` are registered globals in WordPress.

**jQuery access pattern:**

```javascript
(function ($) {
  // Use $ safely here
})(jQuery);
```

**Extending wp object safely:**

```javascript
// At the top of the file
window.wp = window.wp || {};
```

---

## 4. Equality and Type Checks

**Impact: MEDIUM**

### 4.1 Strict Equality

Always use `===` and `!==`.

**Incorrect:**

```javascript
if (foo == bar) {
}
if (foo != bar) {
}
```

**Correct:**

```javascript
if (foo === bar) {
}
if (foo !== bar) {
}
```

### 4.2 Type Checks

**String:**

```javascript
typeof object === "string";
```

**Number:**

```javascript
typeof object === "number";
```

**Boolean:**

```javascript
typeof object === "boolean";
```

**Object:**

```javascript
typeof object === "object";
// or
_.isObject(object);
```

**Plain Object:**

```javascript
jQuery.isPlainObject(object);
```

**Function:**

```javascript
_.isFunction(object);
// or
jQuery.isFunction(object);
```

**Array:**

```javascript
_.isArray(object);
// or
jQuery.isArray(object);
```

**Element:**

```javascript
object.nodeType;
// or
_.isElement(object);
```

**null:**

```javascript
object === null;
```

**null or undefined:**

```javascript
object == null;
```

**undefined:**

```javascript
// Global Variables
typeof variable === "undefined";

// Local Variables
variable === undefined;

// Properties
object.prop === undefined;

// Any of the above
_.isUndefined(object);
```

---

## 5. Syntax Rules

**Impact: MEDIUM**

### 5.1 Semicolons

Always use them. Never rely on Automatic Semicolon Insertion (ASI).

**Incorrect:**

```javascript
var foo = "bar";
function myFunc() {}
```

**Correct:**

```javascript
var foo = "bar";
function myFunc() {}
```

### 5.2 Strings

Use single quotes for string literals.

**Incorrect:**

```javascript
var myStr = "strings should use single quotes";
```

**Correct:**

```javascript
var myStr = "strings should be contained in single quotes";
```

**Escaping:**

```javascript
var escaped = "Note the backslash before the 'single quotes'";
```

### 5.3 Switch Statements

Use `break` for each case except `default`. Indent `case` one tab from `switch`.

**Correct:**

```javascript
switch (event.keyCode) {
  // ENTER and SPACE both trigger x()
  case $.ui.keyCode.ENTER:
  case $.ui.keyCode.SPACE:
    x();
    break;

  case $.ui.keyCode.ESCAPE:
    y();
    break;

  default:
    z();
}
```

**Return values (set in cases, return at end):**

```javascript
function getKeyCode(keyCode) {
  var result;

  switch (event.keyCode) {
    case $.ui.keyCode.ENTER:
    case $.ui.keyCode.SPACE:
      result = "commit";
      break;

    case $.ui.keyCode.ESCAPE:
      result = "exit";
      break;

    default:
      result = "default";
  }

  return result;
}
```

---

## 6. Best Practices

**Impact: LOW**

### 6.1 Comments

Comments come before the code. Preceded by blank line. Single space after `//`.

**Correct:**

```javascript
someStatement();

// Explanation of something complex on the next line
$("p").doSomething();

// This is a comment that is long enough to warrant being stretched
// over the span of multiple lines.
```

**JSDoc format for documentation:**

```javascript
/**
 * Function description.
 *
 * @param {string} param1 - Description.
 * @return {boolean} Description.
 */
function myFunction(param1) {
  return true;
}
```

**Inline comments for special arguments:**

```javascript
function foo(types, selector, data, fn, /* INTERNAL */ one) {
  // Do stuff
}
```

### 6.2 Arrays

Create using `[]` not `new Array()`.

**Correct:**

```javascript
var myArray = [];
var myArray = [1, 2, 3];
```

### 6.3 Objects

Create using `{}` not `new Object()`.

**Correct:**

```javascript
var myObject = {};
var myObject = { key: "value" };
```

### 6.4 Iteration

Use `_.each()` or `jQuery.each()` for collections.

**Correct:**

```javascript
_.each(myArray, function (value, index) {
  // Process value
});

$(".items").each(function () {
  // Process element
});
```

### 6.5 Code Refactoring

Don't refactor just for style. "Whitespace-only" patches are discouraged.

> "Code refactoring should not be done just because we can." – Andrew Nacin

---

## JSHint Configuration

Standard WordPress JSHint settings:

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

**Ignore blocks when needed:**

```javascript
/* jshint ignore:start */
code_that_should_not_be_linted;
/* jshint ignore:end */
```
