---
name: wordpress-accessibility-coding-standards
description: WordPress accessibility coding standards (WCAG 2.2 Level AA). Apply when working with UI elements, forms, or any user-facing code in WordPress plugins or themes.
---

# WordPress Accessibility Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring code in the WordPress ecosystem to ensure accessibility.

---

## Overview

Code integrated into the WordPress ecosystem is expected to conform to **WCAG 2.2 at Level AA**. New interfaces should incorporate ATAG 2.0 guidelines where applicable.

**When to apply:** All WordPress Core, plugins, themes, and WordPress.org websites.

**Reference:** [WordPress Accessibility Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/accessibility/)

---

## Conformance Levels

### Level A — **CRITICAL**

Minimum requirements. Prevents major accessibility barriers.

### Level AA — **REQUIRED**

WordPress commitment level. More nuanced accessibility concerns.

### Level AAA — **ENCOURAGED**

Targeted at specific needs. Implement where relevant.

---

## Rule Categories by WCAG Principle

### 1. Perceivable — **CRITICAL**

| Rule                                | Impact         |
| ----------------------------------- | -------------- |
| [Perceivable](rules/perceivable.md) | Content access |

### 2. Operable — **CRITICAL**

| Rule                          | Impact                |
| ----------------------------- | --------------------- |
| [Operable](rules/operable.md) | Keyboard & navigation |

### 3. Understandable — **HIGH**

| Rule                                      | Impact             |
| ----------------------------------------- | ------------------ |
| [Understandable](rules/understandable.md) | User comprehension |

### 4. Robust — **HIGH**

| Rule                      | Impact        |
| ------------------------- | ------------- |
| [Robust](rules/robust.md) | Compatibility |

### 5. WordPress-Specific — **HIGH**

| Rule                                              | Impact       |
| ------------------------------------------------- | ------------ |
| [WordPress Specific](rules/wordpress-specific.md) | WP ecosystem |

---

## Usage

```text
@wordpress-accessibility-coding-standards
```

See [AGENTS.md](AGENTS.md) for the complete reference.

---

## Normative References

- [W3C WCAG 2.2](https://www.w3.org/TR/WCAG22)
- [W3C ATAG 2.0](https://www.w3.org/TR/ATAG20/)
- [W3C WAI-ARIA 1.1](https://www.w3.org/TR/wai-aria/)
