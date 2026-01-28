# Syntax Rules

**Priority: MEDIUM**  
**Impact: Consistency and ASI prevention**

---

## Semicolons

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

---

## Strings

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

---

## Switch Statements

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
