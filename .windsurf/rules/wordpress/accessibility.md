---
trigger: model_decision
description: WordPress accessibility coding standards (WCAG 2.2 Level AA). Apply when working with UI elements, forms, or any user-facing code.
---

# WordPress Accessibility Coding Standards

Based on WordPress Core Official Standards (WCAG 2.2 Level AA).

**Reference:** [WordPress Accessibility Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/)

---

## Conformance Levels

Code integrated into the WordPress ecosystem is expected to conform to the **Web Content Accessibility Guidelines (WCAG), version 2.2, at level AA**. This is mandatory for all new and updated code.

| Level     | Priority   | Description                        |
| --------- | ---------- | ---------------------------------- |
| Level A   | Critical   | Minimum accessibility requirements |
| Level AA  | Required   | WordPress commitment level         |
| Level AAA | Encouraged | Enhanced accessibility             |

---

## 1. Perceivable

Content must be presentable in ways users can perceive.

### 1.1 Text Alternatives

Provide text alternatives for non-text content.

**Images**

```html
<!-- Informative image -->
<img src="chart.png" alt="Sales chart showing 50% growth in Q4" />

<!-- Decorative image -->
<img src="decorative-border.png" alt="" />

<!-- Complex image with extended description -->
<figure>
	<img
		src="complex-diagram.png"
		alt="System architecture diagram"
		aria-describedby="diagram-desc"
	/>
	<figcaption id="diagram-desc">
		Detailed description of the system architecture...
	</figcaption>
</figure>
```

**Form Controls**

```html
<label for="search">Search</label>
<input type="search" id="search" name="s" />

<!-- Icon button -->
<button type="submit" aria-label="Submit search">
	<span class="dashicons dashicons-search" aria-hidden="true"></span>
</button>
```

**Audio and Video**

- Provide captions for video content
- Provide transcripts for audio content
- Provide audio descriptions for video with important visual information

### 1.2 Adaptable

Create content that can be presented in different ways.

**Semantic Structure**

```html
<!-- Use proper heading hierarchy -->
<h1>Page Title</h1>
<h2>Section Title</h2>
<h3>Subsection Title</h3>

<!-- Use semantic elements -->
<nav aria-label="Main navigation">...</nav>
<main>...</main>
<aside aria-label="Related content">...</aside>

<!-- Use lists for list content -->
<ul>
	<li>Item one</li>
	<li>Item two</li>
</ul>
```

**Reading Order**

Ensure visual order matches DOM order.

**Meaningful Sequence**

Content should make sense when read in order.

### 1.3 Distinguishable

Make content distinguishable from background and other content.

**Color Contrast**

| Text Type                        | Minimum Ratio |
| -------------------------------- | ------------- |
| Normal text                      | 4.5:1         |
| Large text (18pt+ or 14pt+ bold) | 3:1           |
| UI components and graphics       | 3:1           |

**Color Not Sole Indicator**

Never use color alone to convey information.

```css
/* Incorrect - color only */
.error {
	color: #d00;
}

/* Correct - multiple indicators */
.error {
	color: #d00;
	border-left: 4px solid #d00;
}

.error::before {
	content: "Error: ";
	font-weight: 700;
}
```

**Text Resize**

Text must be resizable up to 200% without loss of content or functionality.

**Text Spacing**

Content must remain readable with adjusted spacing:

- Line height: 1.5 times font size
- Paragraph spacing: 2 times font size
- Letter spacing: 0.12 times font size
- Word spacing: 0.16 times font size

---

## 2. Operable

User interface components must be operable.

### 2.1 Keyboard Accessible

All functionality must be available via keyboard.

**Focusable Elements**

```html
<!-- Use native interactive elements -->
<button type="button">Click me</button>
<a href="/page/">Link</a>

<!-- If custom element needed, add keyboard support via JS (no inline handlers) -->
<div
	role="button"
	tabindex="0"
	class="js-custom-button"
>
	Custom Button
</div>
```

Do **not** use inline event handlers (`onclick`, `onkeydown`). Attach all events in JavaScript files.

```javascript
const button = document.querySelector( '.js-custom-button' );
button.addEventListener( 'click', handleClick );
button.addEventListener( 'keydown', function( event ) {
	if ( event.key === 'Enter' || event.key === ' ' ) {
		event.preventDefault();
		handleClick();
	}
} );
```

**Focus Trap Prevention**

Ensure users can navigate away from all components.

**Keyboard Shortcuts**

If implementing shortcuts, allow users to turn them off or remap them.

### 2.2 Enough Time

Give users enough time to read and use content.

- Allow users to turn off time limits
- Allow users to extend time limits
- Warn before timeout

### 2.3 Seizures and Physical Reactions

Do not design content that causes seizures.

- No content that flashes more than 3 times per second
- Provide warnings for flashing content

### 2.4 Navigable

Provide ways to help users navigate and find content.

**Skip Links**

Skip links allow keyboard and screen reader users to bypass repetitive navigation. WordPress Core uses the `.screen-reader-text` class for skip links that become visible on focus.

```html
<!-- Add at the beginning of the body, before navigation -->
<a class="screen-reader-text" href="#main-content"> Skip to main content </a>

<!-- Target element -->
<main id="main-content">
	<!-- Main content -->
</main>
```

The `.screen-reader-text` class (defined in WordPress Specific Practices section) handles both the visually hidden state and the visible-on-focus behavior. No additional CSS is required when using this class.

**Page Titles**

Descriptive and unique page titles.

```html
<title>Edit Post "Hello World" - My WordPress Site</title>
```

**Link Purpose**

Links should be understandable from link text alone.

```html
<!-- Incorrect -->
<a href="/report.pdf">Click here</a>
<a href="/report.pdf">Read more</a>

<!-- Correct -->
<a href="/report.pdf">Download Annual Report (PDF, 2MB)</a>
<a href="/article/">Read more about WordPress accessibility</a>
```

**Multiple Ways**

Provide multiple ways to locate content (navigation, search, sitemap).

**Headings and Labels**

Use descriptive headings and labels.

**Focus Visible**

Focus indicator must be visible.

```css
*:focus {
	outline: 2px solid #0073aa;
	outline-offset: 2px;
}

/* Never remove focus outline without replacement */
*:focus {
	outline: none; /* INCORRECT */
}
```

### 2.5 Input Modalities

Make functionality easier to operate through various inputs.

**Target Size**

Minimum target size of 24x24 CSS pixels. Recommended 44x44 pixels.

```css
.button,
.nav-link {
	min-width: 44px;
	min-height: 44px;
}
```

**Pointer Gestures**

Do not require complex gestures. Provide alternatives.

**Motion Activation**

Do not require device motion. Provide alternatives.

---

## 3. Understandable

Information and UI operation must be understandable.

### 3.1 Readable

Make text content readable and understandable.

**Language of Page**

```html
<html lang="en"></html>
```

**Language of Parts**

```html
<p>
	The French phrase <span lang="fr">c'est la vie</span> means "that's life".
</p>
```

### 3.2 Predictable

Make pages appear and operate predictably.

**On Focus**

No change of context on focus.

```javascript
// Incorrect - auto-submit on focus
input.addEventListener( 'focus', function () {
	form.submit();
} );
```

**On Input**

No unexpected context change on input.

```javascript
// Incorrect - auto-navigate on select change
select.addEventListener( 'change', function () {
	window.location = this.value;
} );

// Correct - require button press
button.addEventListener( 'click', function () {
	window.location = select.value;
} );
```

**Consistent Navigation**

Navigation should appear in same order across pages.

**Consistent Identification**

Components with same function should be identified consistently.

### 3.3 Input Assistance

Help users avoid and correct mistakes.

**Error Identification**

```html
<label for="email">Email (required)</label>
<input
	type="email"
	id="email"
	name="email"
	required="required"
	aria-invalid="true"
	aria-describedby="email-error"
/>
<span id="email-error" class="error-message">
	Please enter a valid email address
</span>
```

**Labels or Instructions**

```html
<label for="date">Date (YYYY-MM-DD)</label>
<input type="text" id="date" name="date" placeholder="2024-01-15" />
```

**Error Suggestion**

Provide specific error messages with suggestions.

```html
<span class="error-message">
	Password must be at least 8 characters and include a number
</span>
```

**Error Prevention**

For legal and financial transactions:

- Allow users to review before submitting
- Allow users to correct errors
- Provide confirmation

---

## 4. Robust

Content must be robust enough for assistive technologies.

### 4.1 Compatible

Maximize compatibility with current and future tools.

**Valid HTML**

Use valid HTML that parses correctly.

**Name, Role, Value**

Ensure all UI components have accessible names and roles.

```html
<!-- Native elements have built-in roles -->
<button>Submit</button>

<!-- Custom widgets need ARIA -->
<div role="tablist" aria-label="Product tabs">
	<button role="tab" id="tab-1" aria-selected="true" aria-controls="panel-1">
		Description
	</button>
	<button role="tab" id="tab-2" aria-selected="false" aria-controls="panel-2">
		Reviews
	</button>
</div>
<div role="tabpanel" id="panel-1" aria-labelledby="tab-1">Panel content...</div>
```

**Status Messages**

Announce status messages to screen readers.

```html
<div role="alert" aria-live="assertive">Form submitted successfully</div>

<div role="status" aria-live="polite">3 results found</div>
```

---

## WordPress Specific Practices

### Screen Reader Text

```css
.screen-reader-text {
	border: 0;
	clip: rect( 1px, 1px, 1px, 1px );
	clip-path: inset( 50% );
	height: 1px;
	margin: -1px;
	overflow: hidden;
	padding: 0;
	position: absolute;
	width: 1px;
	word-wrap: normal !important;
}

.screen-reader-text:focus {
	background-color: #f1f1f1;
	border-radius: 3px;
	box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
	clip: auto !important;
	clip-path: none;
	color: #21759b;
	display: block;
	font-size: 14px;
	font-weight: 700;
	height: auto;
	left: 5px;
	line-height: normal;
	padding: 15px 23px 14px;
	text-decoration: none;
	top: 5px;
	width: auto;
	z-index: 100000;
}
```

### Admin Notices

```php
<div class="notice notice-success is-dismissible">
	<p><?php esc_html_e( 'Settings saved.', 'textdomain' ); ?></p>
</div>
```

### Live Regions

```php
<div
	id="ajax-response"
	aria-live="polite"
	aria-atomic="true"
>
	<?php // Dynamic content updated via JavaScript ?>
</div>
```

```javascript
// Update live region content
document.getElementById( 'ajax-response' ).textContent =
	'Loading complete. 5 items loaded.';
```

### Toggles and Expandables

```html
<button type="button" aria-expanded="false" aria-controls="menu-content">
	Menu
</button>
<div id="menu-content" hidden="hidden">Menu content here</div>
```

```javascript
button.addEventListener( 'click', function () {
	const expanded = this.getAttribute( 'aria-expanded' ) === 'true';
	this.setAttribute( 'aria-expanded', ! expanded );
	content.hidden = expanded;
} );
```

---

## Common Failure Patterns

### Missing Form Labels

```html
<!-- Failure -->
<input type="text" name="email" placeholder="Email" />

<!-- Correct -->
<label for="email">Email</label>
<input type="email" id="email" name="email" />
```

### Empty Links

```html
<!-- Failure -->
<a href="page.html"><img src="icon.png" alt="" /></a>

<!-- Correct -->
<a href="page.html">
	<img src="icon.png" alt="" />
	<span class="screen-reader-text">Go to page</span>
</a>
```

### Missing Skip Link

```html
<!-- Add at beginning of body -->
<a href="#main" class="skip-link screen-reader-text"> Skip to main content </a>
```

### Low Contrast Text

Use contrast checker tools to verify ratios.

### Keyboard Traps

Ensure all modals and popups can be closed with Escape key.

---

## Testing Tools

### Automated

- axe DevTools
- WAVE
- Lighthouse
- Pa11y

### Manual

- Keyboard-only navigation
- Screen reader testing (NVDA, JAWS, VoiceOver)
- High contrast mode
- Zoom to 200%

### User Testing

- Include people with disabilities in testing
