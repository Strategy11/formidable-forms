# Values

**Priority: MEDIUM**  
**Impact: Consistency**

---

## General Rules

- Space after property colon
- Space after commas in multi-value properties
- Lowercase hex values, shorthand when possible
- Avoid units on zero values
- Use leading zero for decimals

---

## Colors

**Incorrect:**

```css
.selector {
    color: #FFFFFF;
    background: #FF0000;
}
```

**Correct:**

```css
.selector {
    color: #fff;
    background: #f00;
}
```

---

## Units

**Incorrect:**

```css
.selector {
    margin: 0px;
    padding: 0em;
    opacity: .5;
}
```

**Correct:**

```css
.selector {
    margin: 0;
    padding: 0;
    opacity: 0.5;
}
```

---

## Multiple Values

Space after each comma.

**Correct:**

```css
.selector {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    background: rgba(0, 0, 0, 0.5);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2), 0 2px 4px rgba(0, 0, 0, 0.1);
}
```

---

## URLs

Quotes around URL paths.

**Correct:**

```css
.selector {
    background: url("images/bg.png");
}
```
