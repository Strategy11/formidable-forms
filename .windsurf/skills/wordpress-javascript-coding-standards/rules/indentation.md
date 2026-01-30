# Indentation and Line Breaks

**Priority: HIGH**  
**Impact: Code structure and consistency**

---

## Tab Indentation

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

---

## Blocks and Curly Braces

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

---

## Multi-line Statements

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

---

## Chained Method Calls

One call per line. Extra indent when context changes.

**Correct:**

```javascript
elements.addClass("foo").children().html("hello").end().appendTo("body");
```
