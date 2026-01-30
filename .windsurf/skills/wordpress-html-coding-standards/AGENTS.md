# WordPress HTML Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring HTML code in the WordPress ecosystem.

---

## Abstract

These HTML coding standards ensure well-formed, accessible markup. Validation helps weed out problems but is no substitute for manual code review.

---

## Table of Contents

1. [Validation](#1-validation) — **HIGH**
2. [Self-closing Elements](#2-self-closing-elements) — **HIGH**
3. [Attributes and Tags](#3-attributes-and-tags) — **MEDIUM**
4. [Quotes](#4-quotes) — **MEDIUM**
5. [Indentation](#5-indentation) — **MEDIUM**

---

## 1. Validation

**Impact: HIGH**

All HTML should be verified against the W3C validator to ensure well-formed markup.

**Tools:**

- [W3C Markup Validation Service](https://validator.w3.org/)
- Browser developer tools

**Note:** Validation alone doesn't guarantee good code—manual review is essential.

---

## 2. Self-closing Elements

**Impact: HIGH**

All tags must be properly closed. Self-closing tags need exactly one space before the slash.

**Incorrect:**

```html
<br />
<img src="image.png" />
<input type="text" />
```

**Correct:**

```html
<br />
<img src="image.png" />
<input type="text" />
```

The W3C specifies that a single space should precede the self-closing slash.

---

## 3. Attributes and Tags

**Impact: MEDIUM**

### 3.1 Lowercase

All tags and attributes must be lowercase.

**Incorrect:**

```html
<div class="Container">
  <input type="TEXT" />
</div>
```

**Correct:**

```html
<div class="container">
  <input type="text" />
</div>
```

### 3.2 Attribute Values

Lowercase for machine-interpreted values. Title case for human-readable values.

**For machines:**

```html
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
```

**For humans:**

```html
<a href="http://example.com/" title="Description Here">Example.com</a>
```

---

## 4. Quotes

**Impact: MEDIUM**

### 4.1 Always Quote Attributes

Use double or single quotes. Never omit quotes—it can cause security vulnerabilities.

**Incorrect:**

```html
<input type="text" name="email" disabled />
```

**Correct:**

```html
<input type="text" name="email" disabled="disabled" />
```

Or with single quotes:

```html
<input type="text" name="email" disabled="disabled" />
```

### 4.2 Boolean Attributes

You may omit the value on boolean attributes, but never use `true` or `false`.

**Incorrect:**

```html
<input type="text" name="email" disabled="true" />
<input type="text" name="email" disabled="false" />
```

**Correct:**

```html
<input type="text" name="email" disabled />
<input type="text" name="email" disabled="disabled" />
```

---

## 5. Indentation

**Impact: MEDIUM**

### 5.1 Use Tabs

HTML indentation should reflect logical structure. Use tabs, not spaces.

### 5.2 PHP in HTML

Indent PHP blocks to match surrounding HTML. Closing PHP blocks should match opening block indentation.

**Incorrect:**

```php
        <?php if ( ! have_posts() ) : ?>
    <div id="post-0" class="post error404 not-found">
<h1 class="entry-title">Not Found</h1>
            <div class="entry-content">
    <p>Apologies, but no results were found.</p>
<?php get_search_form(); ?>
        </div>
</div>
<?php endif; ?>
```

**Correct:**

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

---

## Best Practices Summary

1. **Validate markup** — Use W3C validator
2. **Close all tags** — Including self-closing with space before slash
3. **Lowercase everything** — Tags, attributes, machine values
4. **Quote all attributes** — Double quotes preferred
5. **Boolean attributes** — Omit value or repeat attribute name, never `true`/`false`
6. **Use tabs** — Reflect logical structure
7. **Align PHP with HTML** — Maintain consistent indentation
