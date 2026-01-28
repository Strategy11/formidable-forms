# Properties

**Priority: MEDIUM**  
**Impact: Consistency and readability**

---

## Property Ordering

Group properties logically:

1. **Display & Position** — display, visibility, position, float, clear, overflow, z-index
2. **Box Model** — width, height, margin, padding, border
3. **Typography** — font, line-height, text-*, letter-spacing, word-spacing
4. **Visual** — background, color, list-style
5. **Other** — cursor, content, etc.

**Correct:**

```css
.selector {
    /* Display & Position */
    display: block;
    position: relative;
    float: left;

    /* Box Model */
    width: 100%;
    margin: 10px;
    padding: 10px;
    border: 1px solid #000;

    /* Typography */
    font-family: sans-serif;
    font-size: 16px;
    line-height: 1.4;

    /* Visual */
    background: #fff;
    color: #000;
}
```

---

## Vendor Prefixes

Stack prefixes vertically, aligned. Standard property last.

**Correct:**

```css
.selector {
    -webkit-transition: all 0.3s ease;
       -moz-transition: all 0.3s ease;
        -ms-transition: all 0.3s ease;
         -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
}
```

> **Note:** Use Autoprefixer in your build process to handle prefixes automatically.
