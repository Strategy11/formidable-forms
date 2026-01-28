# Understandable (WCAG Principle 3)

**Priority: HIGH**  
**Impact: Users must be able to understand content and operation**

---

## 3.1 Readable

Make text content readable and understandable.

### Language of Page

```html
<html lang="en">
```

### Language of Parts

```html
<p>The French phrase <span lang="fr">c'est la vie</span> means "that's life."</p>
```

### Unusual Words

Provide definitions for jargon, idioms, and technical terms.

---

## 3.2 Predictable

Make pages appear and operate predictably.

### On Focus

Don't change context when element receives focus.

**Incorrect:**

```javascript
input.addEventListener('focus', function() {
    window.location = '/new-page';
});
```

### On Input

Don't change context when user inputs data (unless warned).

**Incorrect:**

```html
<select onchange="this.form.submit()">
```

**Correct:**

```html
<select id="country" name="country">
    <option value="">Select country</option>
    <!-- options -->
</select>
<button type="submit">Update</button>
```

### Consistent Navigation

Navigation should be in the same relative order across pages.

### Consistent Identification

Components with same functionality should be identified consistently.

---

## 3.3 Input Assistance

Help users avoid and correct mistakes.

### Error Identification

**Incorrect:**

```html
<input type="email" class="error">
```

**Correct:**

```html
<label for="email">Email</label>
<input type="email" id="email" aria-describedby="email-error" aria-invalid="true">
<span id="email-error" class="error">Please enter a valid email address.</span>
```

### Labels or Instructions

Provide clear instructions for required formats.

```html
<label for="phone">Phone Number</label>
<input type="tel" id="phone" aria-describedby="phone-hint">
<span id="phone-hint">Format: (123) 456-7890</span>
```

### Error Suggestion

When errors are detected, provide suggestions for correction.

### Error Prevention

For legal, financial, or data submissions:

- Allow review before final submission
- Provide confirmation/undo capability
- Check input and allow correction
