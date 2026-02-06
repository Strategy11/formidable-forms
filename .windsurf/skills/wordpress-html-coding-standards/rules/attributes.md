# Attributes and Quotes

**Priority: HIGH**  
**Impact: Parsing reliability and consistency**

---

## Quote Usage

Always use double quotes around attribute values, never single quotes or no quotes.

**Incorrect:**

```html
<a href='page.html'>Link</a>
<a href=page.html>Link</a>
<input type=text>
<div class=container>
```

**Correct:**

```html
<a href="page.html">Link</a>
<input type="text">
<div class="container">
```

---

## Boolean Attributes

Boolean attributes should not have a value assigned. The presence of the attribute implies `true`.

**Incorrect:**

```html
<input type="text" disabled="disabled">
<input type="checkbox" checked="checked">
<select multiple="multiple">
<option selected="selected">
```

**Correct:**

```html
<input type="text" disabled>
<input type="checkbox" checked>
<select multiple>
<option selected>
```

---

## Attribute Ordering

For consistency, order attributes as follows:

1. `id`
2. `class`
3. `name`
4. `data-*`
5. `src`, `for`, `type`, `href`, `value`
6. `title`, `alt`
7. `role`, `aria-*`

**Correct:**

```html
<a id="main-link" class="btn btn-primary" href="/page" title="Go to page" role="button">
    Click Here
</a>

<input id="email" class="form-control" name="email" type="email" placeholder="Enter email" required>
```

---

## Custom Data Attributes

Use `data-*` attributes for custom data. Use lowercase with hyphens.

**Correct:**

```html
<div data-user-id="123" data-action="edit">
    Content
</div>
```
