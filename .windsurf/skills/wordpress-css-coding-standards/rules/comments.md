# Comments

**Priority: LOW**  
**Impact: Documentation**

---

## Table of Contents

For longer stylesheets, include a table of contents at the top.

**Correct:**

```css
/**
 * TABLE OF CONTENTS
 *
 * 1. Reset
 * 2. Typography
 * 3. Layout
 * 4. Components
 *    4.1 Buttons
 *    4.2 Forms
 *    4.3 Navigation
 * 5. Utilities
 */
```

---

## Section Headers

Use consistent section dividers.

**Correct:**

```css
/**
 * #.# Section title
 *
 * Description of section.
 */

.selector-1 {
    background: #fff;
}
```

---

## Inline Comments

For single-line clarifications.

**Correct:**

```css
.selector {
    /* Override WP default */
    background: #fff;
    color: #000; /* Matches brand guidelines */
}
```

---

## Multi-line Comments

Use DocBlock style for complex explanations.

**Correct:**

```css
/**
 * Long description explaining rationale,
 * browser support considerations, or
 * other important context.
 *
 * @see https://example.com/reference
 */
```
