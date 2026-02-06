# Variables and Naming

**Priority: MEDIUM**  
**Impact: Consistency and modern JS**

---

## Variable Declarations

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

---

## Naming Conventions

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

---

## Abbreviations and Acronyms

Treat as words in camelCase.

**Correct:**

```javascript
var defined;
var htmlContent;
var jsonData;
var xmlParser;
```

---

## Class Definitions

Use UpperCamelCase.

**Correct:**

```javascript
class MyClass {
  constructor() {}
}
```

---

## Constants

Use SCREAMING_SNAKE_CASE.

**Correct:**

```javascript
const MAX_ITEMS = 100;
const API_URL = "https://api.example.com";
```

---

## Globals

Avoid globals. If unavoidable, set them explicitly via `window`.

**Correct:**

```javascript
window.myGlobal = "value";
```
