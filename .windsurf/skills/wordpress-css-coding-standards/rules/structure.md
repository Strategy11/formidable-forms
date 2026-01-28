# Structure

**Priority: HIGH**  
**Impact: Readability and organization**

---

## General Formatting

Each selector on its own line. Opening brace on same line as last selector. Closing brace on its own line.

**Correct:**

```css
#selector-1,
#selector-2,
#selector-3 {
    background: #fff;
    color: #000;
}
```

---

## Property Formatting

- One property per line
- Indent properties with single tab
- End each declaration with semicolon
- Double quotes around values

**Correct:**

```css
#selector-1 {
    background: #fff;
    color: #000;
}

#selector-2 {
    font-family: "Helvetica Neue", sans-serif;
}
```

---

## Blank Lines

Separate rule sets by one blank line for readability.

**Correct:**

```css
#selector-1 {
    background: #fff;
    color: #000;
}

#selector-2 {
    background: #fff;
    color: #000;
}
```
