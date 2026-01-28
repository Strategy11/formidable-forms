# Selectors

**Priority: HIGH**  
**Impact: Specificity and maintainability**

---

## Selector Naming

Use lowercase, separate words with hyphens. Use human-readable names.

**Incorrect:**

```css
.postHeader {}
.post_header {}
#commentForm {}
#comment_form {}
.u-teleportLeft {}
```

**Correct:**

```css
#comment-form {}
.post-header {}
.comment-form-author {}
```

---

## Selector Structure

- Avoid over-qualified selectors
- No IDs in selectors when possible (higher specificity = harder to override)
- Avoid tag selectors for common elements

**Incorrect:**

```css
div.container {}    /* Over-qualified */
#my-id {}          /* Too specific */
div {}              /* Too generic */
```

**Correct:**

```css
.container {}
.my-class {}
.post-content p {}
```

---

## Attribute Selectors

Always quote attribute values.

**Correct:**

```css
input[type="text"] {}
a[href^="https://"] {}
```
