# Spacing

**Priority: HIGH**  
**Impact: Readability**

---

## General Rules

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

---

## Object Declarations

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

---

## Arrays and Function Calls

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

---

## Control Structures

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
