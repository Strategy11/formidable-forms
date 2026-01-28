# Media Queries

**Priority: MEDIUM**  
**Impact: Responsive design organization**

---

## Placement

Place media queries near relevant rule sets, or at end of document in stylesheet sections.

---

## Formatting

- Opening brace on same line as query
- Contents indented one level
- Closing brace on own line

**Correct:**

```css
@media screen and (min-width: 768px) {
    .selector {
        width: 50%;
    }

    .sidebar {
        display: block;
    }
}
```

---

## Breakpoints

Use consistent breakpoints throughout the project. Prefer min-width (mobile-first).

**Common Breakpoints:**

```css
/* Mobile first approach */
@media screen and (min-width: 480px) { /* Small */ }
@media screen and (min-width: 768px) { /* Medium */ }
@media screen and (min-width: 1024px) { /* Large */ }
@media screen and (min-width: 1200px) { /* Extra large */ }
```
