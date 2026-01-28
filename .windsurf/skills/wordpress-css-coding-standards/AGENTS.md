# WordPress CSS Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring CSS code in the WordPress ecosystem.

---

## Abstract

The WordPress CSS Coding Standards create a baseline for collaboration and review within the WordPress ecosystem. The goal is to create code that is readable, meaningful, consistent, and beautiful.

---

## Table of Contents

1. [Structure](#1-structure) — **HIGH**
2. [Selectors](#2-selectors) — **HIGH**
3. [Properties](#3-properties) — **MEDIUM**
4. [Values](#4-values) — **MEDIUM**
5. [Media Queries](#5-media-queries) — **MEDIUM**
6. [Commenting](#6-commenting) — **LOW**
7. [Best Practices](#7-best-practices) — **LOW**

---

## 1. Structure

**Impact: HIGH**

Maintain high legibility with consistent structure.

### 1.1 Indentation

Use tabs, not spaces.

### 1.2 Spacing Between Blocks

- Two blank lines between sections
- One blank line between blocks in a section

### 1.3 Selector and Property Layout

Each selector on its own line. Property-value pairs on their own line with one tab indentation.

**Incorrect:**

```css
#selector-1,
#selector-2,
#selector-3 {
  background: #fff;
  color: #000;
}

#selector-1 {
  background: #fff;
  color: #000;
}
```

**Correct:**

```css
#selector-1,
#selector-2,
#selector-3 {
  background: #fff;
  color: #000;
}
```

---

## 2. Selectors

**Impact: HIGH**

Balance efficiency with specificity.

### 2.1 Naming Convention

Use lowercase with hyphens. Avoid camelCase and underscores.

**Incorrect:**

```css
#commentForm {
}
#comment_form {
}
```

**Correct:**

```css
#comment-form {
}
```

### 2.2 Human Readable Names

Use descriptive names that explain what the element styles.

**Incorrect:**

```css
#c1-xr {
} /* What is c1-xr? */
```

**Correct:**

```css
#comment-form {
}
.post-title {
}
.sidebar-widget {
}
```

### 2.3 Attribute Selectors

Use double quotes around values.

**Incorrect:**

```css
input[type="text"] {
}
```

**Correct:**

```css
input[type="text"] {
}
```

### 2.4 Avoid Over-qualification

Don't add unnecessary element qualifiers.

**Incorrect:**

```css
div#comment-form {
}
div.container {
}
```

**Correct:**

```css
#comment-form {
}
.container {
}
```

---

## 3. Properties

**Impact: MEDIUM**

### 3.1 Formatting

- Colon followed by a space
- All properties and values lowercase
- End with semicolon

**Incorrect:**

```css
#selector-1 {
  background: #ffffff;
  display: BLOCK;
  margin-left: 20px;
}
```

**Correct:**

```css
#selector-1 {
  background: #fff;
  display: block;
  margin-left: 20px;
}
```

### 3.2 Property Ordering

Group related properties. Recommended order:

1. Display & Box Model
2. Positioning
3. Typography
4. Visual (colors, backgrounds)
5. Misc

```css
.selector {
  /* Display & Box Model */
  display: block;
  width: 100%;
  padding: 10px;
  margin: 0;

  /* Positioning */
  position: relative;
  top: 0;

  /* Typography */
  font-family: sans-serif;
  font-size: 16px;
  line-height: 1.5;

  /* Visual */
  background: #fff;
  color: #333;
  border: 1px solid #ccc;

  /* Misc */
  cursor: pointer;
}
```

### 3.3 Shorthand Properties

Use shorthand for `background`, `border`, `font`, `list-style`, `margin`, and `padding`.

**Incorrect:**

```css
.selector {
  margin-top: 10px;
  margin-right: 20px;
  margin-bottom: 10px;
  margin-left: 20px;
}
```

**Correct:**

```css
.selector {
  margin: 10px 20px;
}
```

**Exception:** When overriding specific values:

```css
.selector {
  margin: 0;
  margin-left: 20px;
}
```

### 3.4 Vendor Prefixes

Include when necessary, standard property last.

```css
.selector {
  -webkit-transform: rotate(45deg);
  -moz-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
```

---

## 4. Values

**Impact: MEDIUM**

### 4.1 Colors

Use hex codes (lowercase, shorthand when possible). Use `rgba()` only when opacity needed.

**Incorrect:**

```css
.selector {
  color: #ffffff;
  background: RGB(255, 255, 255);
}
```

**Correct:**

```css
.selector {
  color: #fff;
  background: rgba(0, 0, 0, 0.5);
}
```

### 4.2 Font Weights

Use numeric values.

**Incorrect:**

```css
.selector {
  font-weight: bold;
  font-weight: normal;
}
```

**Correct:**

```css
.selector {
  font-weight: 700;
  font-weight: 400;
}
```

### 4.3 Line Height

Use unit-less values unless specific pixel value needed.

**Incorrect:**

```css
.selector {
  line-height: 1.5em;
}
```

**Correct:**

```css
.selector {
  line-height: 1.5;
}
```

### 4.4 Zero Values

No units on zero values (except `transition-duration`).

**Incorrect:**

```css
.selector {
  margin: 0px 0px 20px 0px;
}
```

**Correct:**

```css
.selector {
  margin: 0 0 20px;
}
```

### 4.5 Decimal Values

Use leading zero.

**Incorrect:**

```css
.selector {
  opacity: 0.5;
}
```

**Correct:**

```css
.selector {
  opacity: 0.5;
}
```

### 4.6 Quotes

Use double quotes. Required for font names with spaces and `content` property.

**Incorrect:**

```css
.selector {
  font-family:
    Times New Roman,
    serif;
  content: "hello";
}
```

**Correct:**

```css
.selector {
  font-family: "Times New Roman", serif;
  content: "hello";
}
```

### 4.7 URL Values

No quotes needed for simple URLs.

```css
.selector {
  background-image: url(images/bg.png);
}
```

### 4.8 Multi-part Values

Use newlines for complex values like `box-shadow` and `text-shadow`.

**Correct:**

```css
.selector {
  box-shadow:
    0 0 0 1px #5b9dd9,
    0 0 2px 1px rgba(30, 140, 190, 0.8);
}
```

---

## 5. Media Queries

**Impact: MEDIUM**

### 5.1 Placement

Keep media queries grouped at bottom of stylesheet (or at bottom of sections for large files like `wp-admin.css`).

### 5.2 Indentation

Indent rule sets one level inside media query.

**Correct:**

```css
@media all and (max-width: 699px) and (min-width: 520px) {
  .selector {
    display: block;
  }
}
```

---

## 6. Commenting

**Impact: LOW**

### 6.1 Comment Liberally

Use `SCRIPT_DEBUG` constant and minified files in production.

### 6.2 Table of Contents

Use for longer stylesheets with index numbers.

```css
/**
 * Table of Contents
 *
 * 1.0 - Reset
 * 2.0 - Typography
 * 3.0 - Layout
 * 4.0 - Components
 * 5.0 - Media Queries
 */
```

### 6.3 Section Headers

```css
/**
 * 1.0 Reset
 *
 * Description of section, whether or not it has media queries, etc.
 */

.selector {
  float: left;
}
```

### 6.4 Inline Comments

```css
/* This is a comment about this selector */
.another-selector {
  position: absolute;
  top: 0 !important; /* I should explain why this is so !important */
}
```

---

## 7. Best Practices

**Impact: LOW**

### 7.1 Remove Before Adding

When fixing issues, try removing code before adding more.

### 7.2 Avoid Magic Numbers

Don't use arbitrary values as quick fixes.

**Incorrect:**

```css
.box {
  margin-top: 37px; /* Why 37? */
}
```

**Correct:**

```css
.box {
  margin-top: 2rem; /* Consistent spacing unit */
}
```

### 7.3 Target Elements Directly

Use classes on elements instead of parent selectors.

**Incorrect:**

```css
.highlight a {
} /* Selector on parent */
```

**Correct:**

```css
.highlight-link {
} /* Class on the element */
```

### 7.4 Height vs Line-height

Use `height` only for external elements (images). Use `line-height` for text flexibility.

### 7.5 Don't Restate Defaults

Don't declare default values.

**Incorrect:**

```css
div {
  display: block; /* div is already block */
}
```

### 7.6 WP Admin CSS

Follow the same standards. Use `!important` sparingly and document why.
