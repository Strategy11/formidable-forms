---
trigger: "glob"
globs: ["**/*.css", "**/*.scss", "**/*.less"]
description: "WordPress CSS coding standards with Formidable Forms patterns. Auto-applies when working with CSS files."
---

# WordPress CSS Coding Standards

Based on WordPress Core Official Standards and `@wordpress/stylelint-config`. Apply when maintaining, generating, or refactoring CSS code.

**References:**

- [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- [@wordpress/stylelint-config](https://github.com/WordPress/gutenberg/tree/trunk/packages/stylelint-config)

---

## Browser Support

Formidable extends `@wordpress/browserslist-config`. Check `.browserslistrc` for current targets:

```text
extends @wordpress/browserslist-config
```

Do NOT hardcode browser targets. Always reference the project's `.browserslistrc` file for the authoritative list.

---

## Stylelint Configuration

Formidable uses `@wordpress/stylelint-config/scss`. See `.stylelintrc.json`:

```json
{
	"extends": "@wordpress/stylelint-config/scss"
}
```

All CSS/SCSS must pass stylelint before commit.

---

## Stylelint Rules (from @wordpress/stylelint-config)

These rules are enforced by `@wordpress/stylelint-config`:

### Core Rules

| Rule                                     | Value     | Description                  |
| ---------------------------------------- | --------- | ---------------------------- |
| `color-hex-length`                       | `short`   | Use `#fff` not `#ffffff`     |
| `color-named`                            | `never`   | No named colors like `red`   |
| `font-weight-notation`                   | `numeric` | Use `400` not `normal`       |
| `function-url-quotes`                    | `never`   | No quotes in `url()`         |
| `length-zero-no-unit`                    | `true`    | Use `0` not `0px`            |
| `selector-attribute-quotes`              | `always`  | Quotes in `[type="text"]`    |
| `selector-pseudo-element-colon-notation` | `double`  | Use `::before` not `:before` |

### Selector Pattern

Selectors should use lowercase with hyphens:

```css
/* Correct */
.frm-button-primary {
}

.frm-field-container {
}

/* Incorrect */
.frmButtonPrimary {
}

.frm_field_container {
}
```

---

## 1. Structure

### Indentation

Use tabs, not spaces.

### Spacing

- One blank line between rule sets
- Empty line before at-rules (except after blockless)
- Newline after opening brace
- Newline before closing brace

### Selector Layout

Each selector on its own line. Opening brace on same line as last selector.

```css
.frm-selector-1,
.frm-selector-2,
.frm-selector-3 {
	background: #fff;
	color: #000;
}
```

---

## 2. Selectors

### Formidable Naming Convention

Formidable uses the `frm-` prefix for classes:

```css
.frm-button {
}

.frm-field-container {
}

.frm-dashboard-widget {
}

.frm-counter-card {
}
```

### Selector Specificity

Avoid over-qualification. Do not add unnecessary element selectors.

```css
/* Incorrect */
div.frm-container {
}

/* Correct */
.frm-container {
}
```

### Attribute Selectors

Always use quotes around attribute values.

```css
input[type="text"] {
}

input[name="item_meta"] {
}
```

### Selector Depth

Maximum 3-4 levels deep.

```css
/* Too specific */
.frm-dashboard-container .frm-widget .frm-card .frm-title .frm-text {
}

/* Better */
.frm-widget .frm-title {
}
```

---

## 3. Properties

### Formatting

- Colon followed by single space
- All lowercase for property names and values
- Trailing semicolon required
- No space before colon

```css
.frm-button {
	background-color: #fff;
	font-size: 16px;
	text-align: center;
}
```

### Property Ordering

Group related properties:

1. **Display and Box Model** - display, box-sizing, width, height, padding, margin, border
2. **Positioning** - position, top, right, bottom, left, z-index
3. **Typography** - font-family, font-size, font-weight, line-height, text-align, color
4. **Visual** - background, box-shadow, opacity, border-radius
5. **Animation** - transition, transform, animation
6. **Miscellaneous** - cursor, overflow

### Shorthand Properties

Use shorthand when setting all values:

```css
margin: 10px 20px;
padding: var(--gap-sm);
border: 1px solid var(--grey-300);
```

---

## 4. Values

### CSS Custom Properties (Variables)

Formidable uses CSS custom properties extensively. See `resources/scss/admin/base/_variables.scss`:

```css
:root {
	--grey-700: #344054;
	--grey-500: #667085;
	--grey-300: #d0d5dd;
	--primary-500: #4199fd;
	--primary-700: #2b66a9;
	--error-500: #f04438;
	--success-500: #12b76a;
	--small-radius: 8px;
	--gap-xs: 8px;
	--gap-sm: 16px;
	--gap-md: 24px;
	--text-sm: 14px;
	--text-md: 16px;
}

.frm-button {
	background: var(--primary-500);
	padding: var(--gap-sm);
	border-radius: var(--small-radius);
}
```

### Colors

Use hex codes in lowercase shorthand. Use CSS variables when available.

```css
/* Use variables */
color: var(--grey-700);
background: var(--primary-500);

/* Hex shorthand when no variable */
color: #fff;
background: #abc;

/* rgba for transparency */
background: rgba(16, 24, 40, 0.1);
```

### Font Weights

Use numeric values (required by stylelint).

| Weight    | Value |
| --------- | ----- |
| Light     | 300   |
| Normal    | 400   |
| Medium    | 500   |
| Semi-bold | 600   |
| Bold      | 700   |

### Line Height

Use unitless values or CSS variables:

```css
line-height: 1.5;
line-height: var(--leading);
```

### Zero Values

Omit units on zero values (required by stylelint):

```css
margin: 0;
padding: 0 10px;
border: 0;
```

### Decimal Values

Include leading zero (required by stylelint):

```css
opacity: 0.5;
```

### URL Values

No quotes in `url()` (required by stylelint):

```css
background: url(images/background.png);
```

### String Quotes

Use double quotes for strings:

```css
content: "";
font-family: "Helvetica Neue", sans-serif;
```

---

## 5. Media Queries

### Placement

Group media queries at the bottom of the stylesheet or use component-based organization with media queries near related rules.

### Media Query Syntax

Indent rule sets one level inside media query:

```css
@media all and (max-width: 699px) and (min-width: 520px) {
	.frm-selector {
		display: block;
	}
}
```

### Mobile First Approach

```css
.frm-widget {
	width: 100%;
}

@media screen and (min-width: 600px) {
	.frm-widget {
		width: 50%;
	}
}

@media screen and (min-width: 1024px) {
	.frm-widget {
		width: 33.333%;
	}
}
```

---

## 6. Comments

### Section Headers

```css
/**
 * Section Name
 *
 * Description of this section.
 */
```

### Inline Comments

```css
/* Comment before rule */
.frm-button {
	color: var(--grey-700); /* Inline comment */
}
```

---

## 7. Best Practices

### Use CSS Variables

Prefer CSS custom properties over hardcoded values:

```css
/* Incorrect */
.frm-button {
	padding: 16px;
	color: #344054;
}

/* Correct */
.frm-button {
	padding: var(--gap-sm);
	color: var(--grey-700);
}
```

### Avoid Magic Numbers

Document or calculate values:

```css
.frm-overlay {
	/* Offset = header height (50px) - element height (13px) */
	top: 37px;
}
```

### Avoid !important

If used, document why:

```css
/* Override third-party plugin styles */
.frm-override {
	color: var(--grey-700) !important;
}
```

### Box Sizing

Formidable uses `border-box` globally:

```css
*,
*::before,
*::after {
	box-sizing: border-box;
}
```

---

## 8. SCSS Rules

Formidable uses SCSS with `@wordpress/stylelint-config/scss`.

### SCSS-Specific Stylelint Rules

| Rule                                          | Description                 |
| --------------------------------------------- | --------------------------- |
| `scss/at-else-closing-brace-newline-after`    | Newline after closing brace |
| `scss/at-else-empty-line-before`              | No empty line before @else  |
| `scss/selector-no-redundant-nesting-selector` | No redundant `&` nesting    |

### Nesting

Maximum 3 levels deep:

```scss
.frm-block {
	.frm-element {
		.frm-modifier {
			// Stop here
		}
	}
}
```

### Avoid Redundant Nesting

```scss
/* Incorrect */
.frm-button {
	& .frm-icon {
		color: #fff;
	}
}

/* Correct */
.frm-button {
	.frm-icon {
		color: #fff;
	}
}
```

### @if/@else Formatting

```scss
@if $condition {
	color: #fff;
} @else {
	color: #000;
}
```

---

## Tooling

```bash
# Run stylelint check
npx stylelint "**/*.css" "**/*.scss"

# Auto-fix issues
npx stylelint "**/*.css" --fix
```
