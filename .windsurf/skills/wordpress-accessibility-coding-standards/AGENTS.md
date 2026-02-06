# WordPress Accessibility Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring code in the WordPress ecosystem to ensure accessibility.

---

## Abstract

Code integrated into the WordPress ecosystem—including WordPress core, WordPress.org websites, and official plugins—is expected to conform to the Web Content Accessibility Guidelines (WCAG), version 2.2, at level AA.

---

## Table of Contents

1. [Conformance Levels](#1-conformance-levels)
2. [WCAG Principles](#2-wcag-principles)
3. [Guidelines by Principle](#3-guidelines-by-principle)
4. [Success Criteria](#4-success-criteria)
5. [Techniques](#5-techniques)
6. [Authoritative Resources](#6-authoritative-resources)

---

## 1. Conformance Levels

### Level A — Minimum (CRITICAL)

Addresses accessibility barriers on a very wide scale. Prevents many people from accessing the site. These are the **minimum requirements** for most web-based interfaces.

### Level AA — WordPress Requirement (REQUIRED)

The **WordPress commitment level**. These criteria address concerns that are generally more complicated but still common needs with broad reach.

### Level AAA — Enhanced (ENCOURAGED)

Targeted at very specific needs. May be difficult to implement effectively. Implement where relevant and possible.

**Quick Reference:** [WCAG 2.2 Level A and AA Requirements](https://www.w3.org/WAI/WCAG22/quickref/)

---

## 2. WCAG Principles

WCAG 2.2 is organized around 4 principles. Content must be:

### 2.1 Perceivable

Users must be able to perceive the information presented. It cannot be invisible to all their senses.

**Examples:**

- Providing text alternatives for images (alt text)
- Providing captions for videos
- Ensuring sufficient color contrast

### 2.2 Operable

Users must be able to operate the interface. The interface cannot require interaction that a user cannot perform.

**Examples:**

- All functionality available via keyboard
- Users have enough time to read content
- No content that causes seizures

### 2.3 Understandable

Users must be able to understand the information and operation of the interface.

**Examples:**

- Readable text content
- Predictable page operation
- Input assistance for forms

### 2.4 Robust

Content must be robust enough to be interpreted by a wide variety of user agents, including assistive technologies.

**Examples:**

- Valid HTML markup
- ARIA used correctly
- Compatible with current and future technologies

---

## 3. Guidelines by Principle

### Principle 1: Perceivable

**Guideline 1.1 — Text Alternatives**

Provide text alternatives for any non-text content so it can be changed into other forms (large print, braille, speech, symbols, simpler language).

```html
<!-- Incorrect -->
<img src="logo.png" />

<!-- Correct -->
<img src="logo.png" alt="Company Name Logo" />

<!-- Decorative images -->
<img src="decorative-border.png" alt="" />
```

**Guideline 1.2 — Time-based Media**

Provide alternatives for time-based media (audio, video).

- Captions for pre-recorded audio
- Audio descriptions for pre-recorded video
- Transcripts

**Guideline 1.3 — Adaptable**

Create content that can be presented in different ways without losing information or structure.

```html
<!-- Incorrect: visual-only structure -->
<div class="heading">Important Title</div>

<!-- Correct: semantic structure -->
<h2>Important Title</h2>
```

**Guideline 1.4 — Distinguishable**

Make it easier for users to see and hear content.

- **Color contrast:** 4.5:1 minimum for normal text, 3:1 for large text
- **Text resize:** Works up to 200%
- **Don't use color alone** to convey information

```css
/* Incorrect: relies on color alone */
.error {
  color: red;
}

/* Correct: uses multiple indicators */
.error {
  color: #d00;
  border-left: 4px solid #d00;
}
.error::before {
  content: "Error: ";
  font-weight: bold;
}
```

### Principle 2: Operable

**Guideline 2.1 — Keyboard Accessible**

Make all functionality available from a keyboard.

```html
<!-- Incorrect: click-only -->
<div onclick="doAction()">Click me</div>

<!-- Correct: keyboard accessible -->
<button type="button" onclick="doAction()">Click me</button>

<!-- Or with proper ARIA if div is necessary -->
<div
  role="button"
  tabindex="0"
  onclick="doAction()"
  onkeypress="if(event.key==='Enter')doAction()"
>
  Click me
</div>
```

**Guideline 2.2 — Enough Time**

Provide users enough time to read and use content.

- Allow users to turn off, adjust, or extend time limits
- Pause, stop, hide moving/auto-updating content

**Guideline 2.3 — Seizures and Physical Reactions**

Do not design content in a way that causes seizures.

- No content flashing more than 3 times per second

**Guideline 2.4 — Navigable**

Provide ways to help users navigate, find content, and determine where they are.

```html
<!-- Skip link for keyboard users -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Descriptive page titles -->
<title>Contact Us - Company Name</title>

<!-- Descriptive link text -->
<!-- Incorrect -->
<a href="report.pdf">Click here</a>

<!-- Correct -->
<a href="report.pdf">Download Annual Report (PDF)</a>
```

**Guideline 2.5 — Input Modalities**

Make it easier to operate through various inputs beyond keyboard.

- Target size: minimum 24x24 CSS pixels (44x44 recommended)
- Don't require complex gestures

### Principle 3: Understandable

**Guideline 3.1 — Readable**

Make text content readable and understandable.

```html
<!-- Declare language -->
<html lang="en">
  <!-- Mark language changes -->
  <p>
    The French phrase <span lang="fr">c'est la vie</span> means "that's life".
  </p>
</html>
```

**Guideline 3.2 — Predictable**

Make Web pages appear and operate in predictable ways.

- Consistent navigation
- Consistent identification
- No unexpected context changes on focus/input

```html
<!-- Incorrect: auto-submit on change -->
<select onchange="this.form.submit()">
  <!-- Correct: explicit submit -->
  <select name="option">
    <button type="submit">Apply</button>
  </select>
</select>
```

**Guideline 3.3 — Input Assistance**

Help users avoid and correct mistakes.

```html
<!-- Error identification -->
<label for="email">Email (required)</label>
<input
  type="email"
  id="email"
  aria-describedby="email-error"
  aria-invalid="true"
/>
<span id="email-error" class="error">Please enter a valid email address</span>

<!-- Instructions -->
<label for="phone">Phone</label>
<input type="tel" id="phone" aria-describedby="phone-hint" />
<span id="phone-hint">Format: 123-456-7890</span>
```

### Principle 4: Robust

**Guideline 4.1 — Compatible**

Maximize compatibility with user agents and assistive technologies.

```html
<!-- Valid HTML: proper nesting -->
<!-- Incorrect -->
<p><div>Content</div></p>

<!-- Correct -->
<div><p>Content</p></div>

<!-- Proper ARIA usage -->
<!-- Incorrect: redundant ARIA -->
<button role="button">Click</button>

<!-- Correct -->
<button>Click</button>

<!-- ARIA only when needed -->
<div role="alert" aria-live="polite">Form submitted successfully</div>
```

---

## 4. Success Criteria

Each guideline has specific success criteria that must be met. These can be tested using:

1. **Automated tools:** Axe, WAVE, Lighthouse
2. **Manual testing:** Keyboard navigation, screen reader testing
3. **User testing:** Testing with people who have disabilities

### Key Level A Criteria

- 1.1.1 Non-text Content (alt text)
- 1.3.1 Info and Relationships (semantic markup)
- 2.1.1 Keyboard (all functionality)
- 2.4.1 Bypass Blocks (skip links)
- 4.1.2 Name, Role, Value (ARIA)

### Key Level AA Criteria

- 1.4.3 Contrast (Minimum) - 4.5:1
- 1.4.4 Resize Text - 200%
- 2.4.6 Headings and Labels
- 2.4.7 Focus Visible

---

## 5. Techniques

### Sufficient Techniques

Required to meet success criteria.

```php
// WordPress: Always provide alt text
<?php echo wp_get_attachment_image( $id, 'medium', false, array( 'alt' => $alt_text ) ); ?>

// Screen reader text
<span class="screen-reader-text"><?php esc_html_e( 'Search', 'theme-textdomain' ); ?></span>
```

### Advisory Techniques

Go beyond requirements (recommended).

```html
<!-- ARIA landmarks for better navigation -->
<header role="banner">
  <nav role="navigation" aria-label="Main">
    <main role="main">
      <aside role="complementary">
        <footer role="contentinfo"></footer>
      </aside>
    </main>
  </nav>
</header>
```

### Failure Techniques

Patterns that **fail** accessibility requirements.

```html
<!-- FAILURE: Missing form labels -->
<input type="text" name="email" placeholder="Email" />

<!-- FAILURE: Empty links -->
<a href="page.html"><img src="icon.png" alt="" /></a>

<!-- FAILURE: Using only color -->
<span style="color: red;">Required</span>
```

---

## 6. Authoritative Resources

### Normative (Requirements)

- [W3C WCAG 2.2](https://www.w3.org/TR/WCAG22) — Web Content Accessibility Guidelines
- [W3C ATAG 2.0](https://www.w3.org/TR/ATAG20/) — Authoring Tool Accessibility Guidelines
- [W3C WAI-ARIA 1.1](https://www.w3.org/TR/wai-aria/) — Accessible Rich Internet Applications

### Informative (Guidance)

- [Understanding WCAG 2.2](https://www.w3.org/WAI/WCAG22/Understanding/)
- [Using ARIA](https://www.w3.org/TR/using-aria/)
- [ARIA Authoring Practices Guide](https://www.w3.org/WAI/ARIA/apg/) — Design patterns

### Technical Resources

- [WordPress Accessibility Handbook](https://make.wordpress.org/accessibility/handbook/)
- [WordPress Accessibility Team](https://make.wordpress.org/accessibility/)

---

## WordPress-Specific Practices

### Screen Reader Text

```css
.screen-reader-text {
  border: 0;
  clip: rect(1px, 1px, 1px, 1px);
  clip-path: inset(50%);
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
  clip: auto !important;
  clip-path: none;
  display: block;
  height: auto;
  left: 5px;
  padding: 15px 23px 14px;
  top: 5px;
  width: auto;
  z-index: 100000;
}
```

### Skip Links

```html
<a class="skip-link screen-reader-text" href="#main">
  <?php esc_html_e( 'Skip to content', 'theme-textdomain' ); ?>
</a>
```

### Focus Styles

Never remove focus styles without providing an alternative.

```css
/* Incorrect */
*:focus {
  outline: none;
}

/* Correct */
*:focus {
  outline: 2px solid #0073aa;
  outline-offset: 2px;
}
```

### ARIA in WordPress

```php
// Accessible toggle
<button
    aria-expanded="false"
    aria-controls="menu-primary"
    class="menu-toggle"
>
    <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'theme-textdomain' ); ?></span>
</button>

// Live regions for dynamic content
<div aria-live="polite" aria-atomic="true" class="notices">
    <?php // Dynamic notices inserted here ?>
</div>
```
