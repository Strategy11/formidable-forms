# Best Practices

**Priority: LOW**  
**Impact: Documentation and performance**

---

## Comments

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

---

## Arrays

Create using `[]` not `new Array()`.

**Correct:**

```javascript
var myArray = [];
var myArray = [1, 2, 3];
```

---

## Objects

Create using `{}` not `new Object()`.

**Correct:**

```javascript
var myObject = {};
var myObject = { key: "value" };
```

---

## Iteration

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

---

## Code Refactoring

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
