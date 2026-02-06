---
name: wordpress-javascript-coding-standards
description: WordPress JavaScript coding standards for maintaining, generating, or refactoring JS code. Apply when working with JavaScript files in WordPress plugins or themes.
---

# WordPress JavaScript Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring JavaScript code in the WordPress ecosystem.

---

## Overview

JavaScript has become a critical component in WordPress development. These standards ensure consistency and readability across WordPress Core, themes, and plugins.

**When to apply:** Any JavaScript file in WordPress Core, plugins, or themes.

**Reference:** [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

---

## Rule Categories by Priority

### 1. Spacing — **HIGH**

| Rule                        | Impact      |
| --------------------------- | ----------- |
| [Spacing](rules/spacing.md) | Readability |

### 2. Indentation & Line Breaks — **HIGH**

| Rule                                | Impact         |
| ----------------------------------- | -------------- |
| [Indentation](rules/indentation.md) | Code structure |

### 3. Variables & Naming — **MEDIUM**

| Rule                            | Impact      |
| ------------------------------- | ----------- |
| [Variables](rules/variables.md) | Consistency |

### 4. Equality & Type Checks — **MEDIUM**

| Rule                          | Impact         |
| ----------------------------- | -------------- |
| [Equality](rules/equality.md) | Bug prevention |

### 5. Syntax Rules — **MEDIUM**

| Rule                      | Impact         |
| ------------------------- | -------------- |
| [Syntax](rules/syntax.md) | ASI prevention |

### 6. Best Practices — **LOW**

| Rule                                      | Impact        |
| ----------------------------------------- | ------------- |
| [Best Practices](rules/best-practices.md) | Documentation |

---

## Usage

### Load all rules

```text
@wordpress-javascript-coding-standards
```

### Full compiled guide

See [AGENTS.md](AGENTS.md) for the complete reference.

---

## Tooling

Use JSHint with WordPress configuration to check code compliance:

```bash
npm install -g jshint
jshint --config .jshintrc path/to/file.js
```
