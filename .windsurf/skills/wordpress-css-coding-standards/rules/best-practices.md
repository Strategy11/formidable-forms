# Best Practices

**Priority: MEDIUM**  
**Impact: Performance and maintainability**

---

## Avoid !important

Only use when absolutely necessary. Indicates specificity problems.

**Incorrect:**

```css
.selector {
    color: red !important;
}
```

**Correct:**

```css
/* Increase specificity instead */
.parent .selector {
    color: red;
}
```

---

## Shorthand Properties

Use shorthand when setting all values. Use longhand for partial overrides.

**Correct:**

```css
/* All four values */
.selector {
    margin: 10px 20px 10px 20px;
}

/* Just top margin */
.selector {
    margin-top: 10px;
}
```

---

## Avoid Magic Numbers

Document or use variables for non-obvious values.

**Incorrect:**

```css
.selector {
    top: 37px;
}
```

**Correct:**

```css
.selector {
    /* header height (32px) + spacing (5px) */
    top: 37px;
}
```

---

## Performance

- Avoid universal selectors (`*`)
- Avoid deeply nested selectors (max 3 levels)
- Minimize redundancy
- Group common declarations

**Incorrect:**

```css
body * { box-sizing: border-box; }
.nav .menu .item .link .text { color: red; }
```

**Correct:**

```css
*,
*::before,
*::after {
    box-sizing: border-box;
}

.nav-link-text {
    color: red;
}
```

---

## Code Refactoring

Avoid whitespace-only patches. Don't refactor purely for style.

> "Code refactoring should not be done just because we can."
