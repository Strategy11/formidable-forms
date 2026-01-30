# Operable (WCAG Principle 2)

**Priority: CRITICAL**  
**Impact: Users must be able to operate the interface**

---

## 2.1 Keyboard Accessible

All functionality must be available via keyboard.

### Focus Management

**Correct:**

```html
<button type="button">Click Me</button>
<a href="/page">Link</a>
<input type="text">
```

### Custom Interactive Elements

**Incorrect:**

```html
<div onclick="doSomething()">Click Me</div>
```

**Correct:**

```html
<button type="button" onclick="doSomething()">Click Me</button>

<!-- Or if div must be used -->
<div role="button" tabindex="0" onclick="doSomething()" onkeydown="handleKeydown(event)">
    Click Me
</div>
```

### Skip Links

```html
<a href="#main-content" class="skip-link">Skip to main content</a>
<!-- ... navigation ... -->
<main id="main-content">
    <!-- content -->
</main>
```

---

## 2.2 Enough Time

Provide users enough time to read and use content.

- Allow users to turn off, adjust, or extend time limits
- Pause, stop, or hide moving content
- No timing on essential activities

---

## 2.3 Seizures and Physical Reactions

Do not design content that causes seizures.

- No content flashing more than 3 times per second
- Warn users before auto-playing video with flashing

---

## 2.4 Navigable

Help users navigate and find content.

### Page Titles

```html
<title>Contact Us - Company Name</title>
```

### Focus Order

Tab order should follow visual/logical order.

### Link Purpose

**Incorrect:**

```html
<a href="/article">Click here</a>
<a href="/article">Read more</a>
```

**Correct:**

```html
<a href="/article">Read the full article about accessibility</a>
```

### Multiple Ways

Provide multiple ways to find pages (navigation, search, sitemap).

### Headings and Labels

Use descriptive headings that describe topic or purpose.

---

## 2.5 Input Modalities

Make it easier to operate through various inputs beyond keyboard.

### Target Size

- Minimum 44x44 CSS pixels for touch targets
- Adequate spacing between targets

### Motion Actuation

Don't require device motion (shaking, tilting) as sole input method.
