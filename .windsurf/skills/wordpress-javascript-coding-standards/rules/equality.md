# Equality and Type Checks

**Priority: MEDIUM**  
**Impact: Bug prevention and reliability**

---

## Strict Equality

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

---

## Type Checks

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
