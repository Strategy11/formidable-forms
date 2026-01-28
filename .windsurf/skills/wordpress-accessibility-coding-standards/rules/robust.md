# Robust (WCAG Principle 4)

**Priority: HIGH**  
**Impact: Content must work with current and future technologies**

---

## 4.1 Compatible

Maximize compatibility with current and future user agents.

### Parsing (Valid HTML)

- Elements have complete start and end tags
- Elements are nested according to specification
- No duplicate attributes
- IDs are unique

**Incorrect:**

```html
<div id="main">
    <p>Paragraph without closing tag
    <div id="main">Duplicate ID</div>
</div>
```

**Correct:**

```html
<div id="main">
    <p>Paragraph with closing tag</p>
    <div id="secondary">Unique ID</div>
</div>
```

### Name, Role, Value

For all UI components:

- **Name:** Accessible name via label, aria-label, or aria-labelledby
- **Role:** Native HTML role or ARIA role
- **Value/State:** Current value and state programmatically available

**Incorrect:**

```html
<div class="checkbox" onclick="toggle()"></div>
```

**Correct:**

```html
<input type="checkbox" id="agree" name="agree">
<label for="agree">I agree to the terms</label>

<!-- Or with ARIA if custom -->
<div role="checkbox" 
     aria-checked="false" 
     aria-labelledby="agree-label"
     tabindex="0"
     onclick="toggle()"
     onkeydown="handleKey(event)">
</div>
<span id="agree-label">I agree to the terms</span>
```

---

## Status Messages

Convey status messages to assistive technologies without focus change.

**Correct:**

```html
<div role="status" aria-live="polite">
    Your changes have been saved.
</div>

<div role="alert" aria-live="assertive">
    Error: Please correct the form fields.
</div>
```

### Live Regions

- `aria-live="polite"` — Announce when user is idle
- `aria-live="assertive"` — Announce immediately (use sparingly)
- `role="status"` — Polite live region for status messages
- `role="alert"` — Assertive live region for errors/warnings
