---
trigger: glob
globs: ["**/*.css", "**/*.scss", "**/*.less"]
description: WordPress CSS coding standards. Auto-applies when working with CSS files.
---

# WordPress CSS Coding Standards

Based on WordPress Core Official Standards. Apply when maintaining, generating, or refactoring CSS code.

**Reference:** [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

---

## 1. Structure

### Indentation

Use tabs, not spaces.

### Spacing Between Blocks

- Two blank lines between sections
- One blank line between blocks in a section

### Selector and Property Layout

Each selector on its own line. Property-value pairs on own line with one tab indentation.

```css
#selector-1,
#selector-2,
#selector-3 {
  background: #fff;
  color: #000;
}
```

### Closing Brace

Closing brace on its own line at same indentation level as opening selector.

```css
.selector {
  property: value;
}
```

---

## 2. Selectors

### Naming Convention

Lowercase letters and hyphens only. No camelCase or underscores.

```css
#comment-form {
}
.post-title {
}
.sidebar-widget {
}
```

### Selector Specificity

Avoid over-qualification. Do not add unnecessary element selectors.

```css
/* Incorrect */
div#comment-form {
}
ul.nav-menu {
}

/* Correct */
#comment-form {
}
.nav-menu {
}
```

### Attribute Selectors

Use double quotes around attribute values.

```css
input[type="text"] {
}
a[href^="https://"]
{
}
```

### Selector Length

Keep selectors short. Maximum 3-4 levels deep.

```css
/* Too specific */
body .page-wrapper .content-area .post-list .post-item .post-title {
}

/* Better */
.post-list .post-title {
}
```

### Universal Selector

Avoid universal selector `*` except for specific resets.

### ID Selectors

Use sparingly. Prefer classes for reusability.

---

## 3. Properties

### Formatting

- Colon followed by a single space
- All lowercase for property names and values
- End with semicolon

```css
.selector {
  background-color: #fff;
  font-size: 16px;
  text-align: center;
}
```

### Property Ordering

Group related properties together in this order:

1. Display and Box Model
2. Positioning
3. Typography
4. Visual (colors, backgrounds)
5. Animation
6. Miscellaneous

```css
.selector {
  /* Display and Box Model */
  display: block;
  box-sizing: border-box;
  width: 100%;
  height: auto;
  padding: 10px;
  margin: 0 auto;
  border: 1px solid #ccc;

  /* Positioning */
  position: relative;
  top: 0;
  left: 0;
  z-index: 10;

  /* Typography */
  font-family: "Helvetica Neue", sans-serif;
  font-size: 16px;
  font-weight: 400;
  line-height: 1.5;
  text-align: left;
  text-transform: none;
  color: #333;

  /* Visual */
  background-color: #fff;
  background-image: url("image.png");
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  opacity: 1;

  /* Animation */
  transition: all 0.3s ease;
  transform: translateX(0);

  /* Miscellaneous */
  cursor: pointer;
  overflow: hidden;
}
```

### Shorthand Properties

Use shorthand for `background`, `border`, `font`, `margin`, `padding` when setting all values.

```css
/* Shorthand */
margin: 10px 20px;
padding: 10px;
border: 1px solid #ccc;

/* Individual properties when setting one value */
margin-top: 10px;
padding-left: 20px;
border-bottom: 2px solid #000;
```

### Vendor Prefixes

Place standard property last.

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

### Colors

Use hex codes in lowercase. Use shorthand when possible.

```css
/* Correct */
color: #fff;
background: #aabbcc;

/* For transparency use rgba */
background: rgba(0, 0, 0, 0.5);
```

### Font Weights

Use numeric values.

| Weight     | Value |
| ---------- | ----- |
| Normal     | 400   |
| Bold       | 700   |
| Light      | 300   |
| Extra Bold | 800   |

```css
font-weight: 400;
font-weight: 700;
```

### Line Height

Use unitless values for line-height.

```css
/* Correct */
line-height: 1.5;

/* Incorrect */
line-height: 24px;
line-height: 150%;
```

### Zero Values

Omit units on zero values except for `transition-duration`.

```css
margin: 0;
padding: 0 10px;
border: 0;

/* Exception */
transition-duration: 0s;
```

### Decimal Values

Include leading zero for decimals less than 1.

```css
opacity: 0.5;
```

### Quotes

Use double quotes. Required for font names with spaces, URL values, and attribute selectors.

```css
font-family: "Times New Roman", serif;
background: url("image.png");
content: "";
```

### URL Values

Do not use quotes in url() for simple paths.

```css
/* Correct */
background: url(images/background.png);

/* Also correct for paths with special characters */
background: url("images/my image.png");
```

---

## 5. Media Queries

### Placement

Group media queries at the bottom of the stylesheet or use component-based organization with media queries near related rules.

### Indentation

Indent rule sets one level inside media query.

```css
@media all and (max-width: 699px) and (min-width: 520px) {
  .selector {
    display: block;
  }
}
```

### Common Breakpoints

```css
/* Mobile first approach */
.selector {
  width: 100%;
}

@media screen and (min-width: 600px) {
  .selector {
    width: 50%;
  }
}

@media screen and (min-width: 1024px) {
  .selector {
    width: 33.333%;
  }
}
```

### Feature Queries

```css
@supports (display: grid) {
  .container {
    display: grid;
  }
}
```

---

## 6. Commenting

### Table of Contents

Include at the top of major stylesheets.

```css
/**
 * Table of Contents
 *
 * 1.0 Reset
 * 2.0 Typography
 * 3.0 Elements
 * 4.0 Forms
 * 5.0 Navigation
 * 6.0 Accessibility
 * 7.0 Widgets
 * 8.0 Content
 * 9.0 Media Queries
 */
```

### Section Headers

```css
/**
 * 1.0 Reset
 *
 * Resetting and rebuilding styles have been
 * having together so they can operate on a
 * common base.
 */
```

### Subsection Headers

```css
/**
 * 1.1 Reset Lists
 */
```

### Inline Comments

```css
/* This is a comment about the following rule */
.selector {
  property: value; /* This is a comment about this property */
}
```

### Multi-line Comments

```css
/**
 * This is a longer comment that spans
 * multiple lines. Use this format for
 * detailed explanations.
 */
```

---

## 7. Best Practices

### Remove Before Adding

Remove unused code before adding new code.

### Magic Numbers

Avoid unexplained numbers. Document or calculate values.

```css
/* Incorrect */
.selector {
  top: 37px;
}

/* Correct */
.selector {
  /* Offset = header height (50px) - element height (13px) */
  top: 37px;
}
```

### Direct Targeting

Target elements directly with classes rather than relying on element position.

```css
/* Incorrect */
.nav li:nth-child(3) a {
}

/* Correct */
.nav-contact-link {
}
```

### Line Height for Text

Use `line-height` for vertically centering text. Use `height` only for elements with fixed dimensions.

### Default Values

Do not restate default values unless intentionally overriding.

### Important Declaration

Avoid `!important`. If used, document why.

```css
/* Override third-party plugin styles */
.plugin-element {
  color: #333 !important;
}
```

### Box Model

Use `box-sizing: border-box` for predictable sizing.

```css
*,
*::before,
*::after {
  box-sizing: border-box;
}
```

---

## 8. SCSS/Sass Specific

### Nesting

Maximum 3 levels deep.

```scss
.block {
  .element {
    .modifier {
      // Stop here
    }
  }
}
```

### Variables

Use descriptive names.

```scss
$color-primary: #0073aa;
$font-size-base: 16px;
$spacing-unit: 8px;
```

### Mixins

```scss
@mixin button-style($bg-color, $text-color) {
  background: $bg-color;
  color: $text-color;
  padding: 10px 20px;
  border: none;
  cursor: pointer;
}
```

### Extend

Use sparingly and prefer mixins.

```scss
%clearfix {
  &::after {
    content: "";
    display: table;
    clear: both;
  }
}
```

---

## Tooling

```bash
# Stylelint with WordPress config
npm install --save-dev stylelint @wordpress/stylelint-config

# .stylelintrc.json
{
    "extends": "@wordpress/stylelint-config"
}

# Run check
npx stylelint "**/*.css"
```
