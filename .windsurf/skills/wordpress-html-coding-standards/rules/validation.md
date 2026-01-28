# Validation

**Priority: HIGH**  
**Impact: Standards compliance and cross-browser compatibility**

---

## General Rule

All HTML pages should be verified against the W3C validator to ensure markup is well-formed.

---

## Validation Requirements

- No errors in final production code
- Warnings should be reviewed and addressed where appropriate
- Use appropriate DOCTYPE declaration

---

## DOCTYPE

Always use the HTML5 DOCTYPE.

**Correct:**

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Page Title</title>
  </head>
  <body>
    <!-- Content -->
  </body>
</html>
```

---

## Validation Tools

- **W3C Markup Validation Service:** <https://validator.w3.org/>
- **Browser DevTools:** Check for parsing errors in console
- **IDE/Editor plugins:** Real-time validation feedback
