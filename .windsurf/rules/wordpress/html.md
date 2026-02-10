---
trigger: glob
globs: ["**/*.html", "**/*.htm"]
description: WordPress HTML coding standards. Auto-applies when working with HTML files.
---

# WordPress HTML Coding Standards

Based on WordPress Core Official Standards. Apply when maintaining, generating, or refactoring HTML code.

**Reference:** [WordPress HTML Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/html/)

---

## 1. Validation

All HTML pages should be verified against the W3C validator to ensure markup is well-formed.

**Tools:**

- [W3C Markup Validation Service](https://validator.w3.org/)
- Browser developer tools
- IDE extensions

**Key validation checks:**

- Properly nested elements
- Required attributes present
- No duplicate IDs
- Correct doctype declaration

---

## 2. Document Structure

### Doctype

Always use HTML5 doctype.

```html
<!DOCTYPE html>
```

### Language Attribute

Specify the language on the html element.

```html
<html lang="en">
```

### Character Encoding

Declare UTF-8 encoding early in the head.

```html
<head>
    <meta charset="UTF-8" />
</head>
```

### Viewport

Include viewport meta for responsive design.

```html
<meta name="viewport" content="width=device-width, initial-scale=1" />
```

---

## 3. Self-closing Elements

All tags must be properly closed. Self-closing elements require a space before the closing slash.

### Void Elements

These elements are self-closing and should have a space before the slash.

```html
<br />
<hr />
<img src="image.png" alt="Description" />
<input type="text" name="field" />
<meta charset="UTF-8" />
<link rel="stylesheet" href="style.css" />
```

### Incorrect

```html
<!-- Missing space -->
<br/>
<img src="image.png"/>

<!-- Missing closing slash -->
<br>
<input type="text">
```

---

## 4. Attributes

### Lowercase

All attribute names must be lowercase.

```html
<!-- Correct -->
<div class="container" id="main">

<!-- Incorrect -->
<div Class="container" ID="main">
```

### Attribute Values

- Use lowercase for machine-interpreted values
- Use Title Case for human-readable values

```html
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<a href="http://example.com/" title="Description Here">Example</a>
```

### Required Attributes

Always include required attributes for elements.

| Element | Required Attributes |
|---------|---------------------|
| `img` | `src`, `alt` |
| `a` | `href` |
| `input` | `type`, `name` |
| `label` | `for` (when not wrapping input) |
| `form` | `action`, `method` |
| `script` | `src` (for external) |
| `link` | `rel`, `href` |

```html
<img src="photo.jpg" alt="A sunset over mountains" />
<a href="/about/">About Us</a>
<input type="email" name="user_email" id="user_email" />
<label for="user_email">Email Address</label>
```

---

## 5. Quotes

### Always Quote Attributes

Never omit quotes around attribute values. Unquoted attributes can cause security vulnerabilities.

```html
<!-- Correct -->
<input type="text" name="email" disabled="disabled" />

<!-- Incorrect -->
<input type=text name=email disabled=disabled />
```

### Use Double Quotes

Prefer double quotes for attribute values.

```html
<div class="container" data-info="value">
```

### Boolean Attributes

Omit the value or repeat the attribute name. Never use true or false.

```html
<!-- Correct -->
<input type="text" disabled />
<input type="text" disabled="disabled" />
<input type="checkbox" checked="checked" />

<!-- Incorrect -->
<input type="text" disabled="true" />
<input type="checkbox" checked="false" />
```

---

## 6. Tags

### Lowercase

All tag names must be lowercase.

```html
<!-- Correct -->
<div>
    <p>Content</p>
</div>

<!-- Incorrect -->
<DIV>
    <P>Content</P>
</DIV>
```

### Proper Nesting

Elements must be properly nested and closed.

```html
<!-- Correct -->
<p><strong>Bold text</strong></p>

<!-- Incorrect -->
<p><strong>Bold text</p></strong>
```

### Semantic Elements

Use semantic HTML5 elements where appropriate.

```html
<header>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
        </ul>
    </nav>
</header>

<main>
    <article>
        <header>
            <h1>Article Title</h1>
        </header>
        <section>
            <p>Content here.</p>
        </section>
    </article>
    <aside>
        <h2>Related Content</h2>
    </aside>
</main>

<footer>
    <p>Copyright information</p>
</footer>
```

---

## 7. Indentation

### Use Tabs

HTML indentation should reflect the logical structure using tabs.

```html
<div class="wrapper">
    <header>
        <h1>Title</h1>
    </header>
    <main>
        <article>
            <p>Content</p>
        </article>
    </main>
</div>
```

### PHP in HTML

Indent PHP blocks to match surrounding HTML structure.

```php
<?php if ( ! have_posts() ) : ?>
    <div id="post-1" class="post">
        <h1 class="entry-title">Not Found</h1>
        <div class="entry-content">
            <p>Apologies, but no results were found.</p>
            <?php get_search_form(); ?>
        </div>
    </div>
<?php endif; ?>
```

### Inline Elements

Keep short inline elements on same line.

```html
<p>This is <strong>bold</strong> and <em>italic</em> text.</p>
```

### Block Elements

Put block elements on their own lines.

```html
<div>
    <p>First paragraph.</p>
    <p>Second paragraph.</p>
</div>
```

---

## 8. Forms

### Form Structure

```html
<form action="/submit" method="post">
    <fieldset>
        <legend>Personal Information</legend>

        <div class="form-field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required="required" />
        </div>

        <div class="form-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required="required" />
        </div>
    </fieldset>

    <button type="submit">Submit</button>
</form>
```

### Labels

Always associate labels with form controls.

```html
<!-- Using for attribute -->
<label for="username">Username</label>
<input type="text" id="username" name="username" />

<!-- Wrapping the input -->
<label>
    <input type="checkbox" name="agree" /> I agree to the terms
</label>
```

### Input Types

Use appropriate input types for better UX and validation.

| Type | Use Case |
|------|----------|
| `email` | Email addresses |
| `tel` | Phone numbers |
| `url` | URLs |
| `number` | Numeric input |
| `date` | Date selection |
| `search` | Search fields |
| `password` | Passwords |

---

## 9. Tables

### Structure

```html
<table>
    <caption>Monthly Sales</caption>
    <thead>
        <tr>
            <th scope="col">Month</th>
            <th scope="col">Sales</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>January</td>
            <td>$10,000</td>
        </tr>
        <tr>
            <td>February</td>
            <td>$12,000</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th scope="row">Total</th>
            <td>$22,000</td>
        </tr>
    </tfoot>
</table>
```

### Scope Attribute

Use scope attribute on header cells.

```html
<th scope="col">Column Header</th>
<th scope="row">Row Header</th>
```

---

## 10. Links and Images

### Links

```html
<!-- External link -->
<a href="https://example.com/" rel="noopener noreferrer" target="_blank">
    External Site
</a>

<!-- Internal link -->
<a href="/about/">About Us</a>

<!-- Email link -->
<a href="mailto:contact@example.com">Contact Us</a>

<!-- Skip link for accessibility -->
<a href="#main-content" class="skip-link">Skip to content</a>
```

### Images

```html
<!-- Informative image -->
<img src="chart.png" alt="Sales increased 50% in Q4" />

<!-- Decorative image -->
<img src="divider.png" alt="" />

<!-- Figure with caption -->
<figure>
    <img src="photo.jpg" alt="Mountain landscape at sunset" />
    <figcaption>Rocky Mountains, Colorado</figcaption>
</figure>

<!-- Responsive image -->
<img
    src="small.jpg"
    srcset="small.jpg 480w, medium.jpg 800w, large.jpg 1200w"
    sizes="(max-width: 600px) 480px, (max-width: 1000px) 800px, 1200px"
    alt="Responsive image example"
/>
```

---

## 11. Scripts and Styles

### Script Placement

Place scripts at the end of body or use defer attribute.

```html
<body>
    <!-- Content -->

    <script src="script.js"></script>
</body>
```

Or with defer:

```html
<head>
    <script src="script.js" defer="defer"></script>
</head>
```

### Inline Scripts and Styles

Avoid inline scripts and styles. Use external files.

```html
<!-- Incorrect -->
<div style="color: red;">Text</div>
<button onclick="doSomething()">Click</button>

<!-- Correct -->
<div class="error-text">Text</div>
<button class="action-button">Click</button>
```

---

## 12. Comments

```html
<!-- Single line comment -->

<!--
    Multi-line comment
    spanning several lines
-->

<!-- Start: Navigation -->
<nav>...</nav>
<!-- End: Navigation -->
```

---

## Best Practices Summary

1. **Validate markup** using W3C validator
2. **Close all tags** including self-closing with space before slash
3. **Lowercase everything** for tags, attributes, and machine values
4. **Quote all attributes** using double quotes
5. **Boolean attributes** omit value or repeat name
6. **Use tabs** for indentation reflecting logical structure
7. **Align PHP with HTML** maintaining consistent indentation
8. **Use semantic elements** for document structure
9. **Associate labels** with form controls
10. **Include alt text** for images
