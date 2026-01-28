# Perceivable (WCAG Principle 1)

**Priority: CRITICAL**  
**Impact: Users must be able to perceive content**

---

## 1.1 Text Alternatives

Provide text alternatives for non-text content.

### Images

**Incorrect:**

```html
<img src="logo.png">
<img src="chart.png" alt="">
```

**Correct:**

```html
<img src="logo.png" alt="Company Name Logo">
<img src="chart.png" alt="Sales chart showing 25% growth in Q4">
```

### Decorative Images

```html
<img src="decorative-border.png" alt="" role="presentation">
```

### Icons with Actions

```html
<button aria-label="Close dialog">
    <svg aria-hidden="true"><!-- icon --></svg>
</button>
```

---

## 1.2 Time-based Media

Provide alternatives for audio and video content.

- **Captions** for audio content in video
- **Audio descriptions** for visual content
- **Transcripts** for audio-only content

---

## 1.3 Adaptable

Create content that can be presented in different ways.

### Semantic Structure

**Incorrect:**

```html
<div class="heading">Page Title</div>
<div class="big-text">Section</div>
```

**Correct:**

```html
<h1>Page Title</h1>
<h2>Section</h2>
```

### Reading Order

Content should make sense when CSS is disabled.

### Form Labels

**Incorrect:**

```html
<input type="text" placeholder="Email">
```

**Correct:**

```html
<label for="email">Email</label>
<input type="text" id="email" name="email">
```

---

## 1.4 Distinguishable

Make it easy to see and hear content.

### Color Contrast

- **Normal text:** 4.5:1 minimum ratio
- **Large text (18pt+ or 14pt bold):** 3:1 minimum ratio
- **UI components:** 3:1 minimum ratio

### Don't Use Color Alone

**Incorrect:**

```html
<p>Required fields are marked in red.</p>
```

**Correct:**

```html
<p>Required fields are marked with an asterisk (*).</p>
<label for="name">Name *</label>
```

### Text Resize

Content must be readable at 200% zoom without loss of functionality.
