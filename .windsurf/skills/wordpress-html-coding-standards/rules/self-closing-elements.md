# Self-Closing Elements

**Priority: MEDIUM**  
**Impact: Consistency and standards compliance**

---

## Void Elements

Void elements (self-closing) should NOT have a trailing slash in HTML5.

**Void elements include:**

- `<br>`
- `<hr>`
- `<img>`
- `<input>`
- `<link>`
- `<meta>`
- `<area>`
- `<base>`
- `<col>`
- `<embed>`
- `<param>`
- `<source>`
- `<track>`
- `<wbr>`

---

## Examples

**Incorrect (XHTML style):**

```html
<br />
<hr />
<img src="image.png" />
<input type="text" />
<meta charset="UTF-8" />
```

**Correct (HTML5 style):**

```html
<br>
<hr>
<img src="image.png" alt="Description">
<input type="text">
<meta charset="UTF-8">
```

---

## Exception

When working with XHTML documents or XML-based systems (like some WordPress template systems), the trailing slash may be required for valid XML parsing.
