---
name: wordpress-php-coding-standards
description: WordPress PHP coding standards for maintaining, generating, or refactoring PHP code. Apply when working with PHP files in WordPress plugins or themes.
---

# WordPress PHP Coding Standards

**Version 1.0.0**  
Based on WordPress Core Official Standards

> **Note:**  
> This document is for AI agents and LLMs to follow when maintaining,  
> generating, or refactoring PHP code in the WordPress ecosystem.

---

## Overview

These PHP coding standards are the official WordPress coding standards. They are mandatory for WordPress Core and recommended for all plugins and themes. Beyond code style, they encompass best practices for interoperability, translatability, and security.

**When to apply:** Any PHP file in WordPress Core, plugins, or themes.

**Reference:** [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)

---

## Rule Categories by Priority

### 1. Security & Database — **CRITICAL**

Fundamental security practices that prevent vulnerabilities.

| Rule                                          | Impact                 |
| --------------------------------------------- | ---------------------- |
| [Database Queries](rules/database-queries.md) | Prevents SQL injection |

### 2. Naming Conventions — **HIGH**

Consistent naming ensures code readability and discoverability.

| Rule                                              | Impact           |
| ------------------------------------------------- | ---------------- |
| [Naming Conventions](rules/naming-conventions.md) | Code consistency |

### 3. Formatting — **HIGH**

Structural formatting for readable and maintainable code.

| Rule                              | Impact         |
| --------------------------------- | -------------- |
| [Formatting](rules/formatting.md) | Code structure |

### 4. Whitespace & Indentation — **MEDIUM**

Spacing rules for visual consistency.

| Rule                              | Impact      |
| --------------------------------- | ----------- |
| [Whitespace](rules/whitespace.md) | Readability |

### 5. Control Structures — **MEDIUM**

Rules for conditionals and loops.

| Rule                                              | Impact         |
| ------------------------------------------------- | -------------- |
| [Control Structures](rules/control-structures.md) | Bug prevention |

### 6. Operators — **MEDIUM**

Proper operator usage.

| Rule                            | Impact         |
| ------------------------------- | -------------- |
| [Operators](rules/operators.md) | Error handling |

### 7. Best Practices — **MEDIUM**

Recommendations for maintainable code.

| Rule                                      | Impact          |
| ----------------------------------------- | --------------- |
| [Best Practices](rules/best-practices.md) | Maintainability |

### 8. General Syntax — **LOW**

Basic PHP syntax rules.

| Rule                                      | Impact        |
| ----------------------------------------- | ------------- |
| [General Syntax](rules/general-syntax.md) | Compatibility |

### 9. Object-Oriented Programming — **MEDIUM**

OOP guidelines and patterns.

| Rule                | Impact          |
| ------------------- | --------------- |
| [OOP](rules/oop.md) | OOP consistency |

### 10. Namespaces & Imports — **MEDIUM**

Modern PHP namespace and import conventions for plugins and themes.

| Rule                                                | Impact            |
| --------------------------------------------------- | ----------------- |
| [Namespaces & Imports](rules/namespaces-imports.md) | Code organization |

### 11. Shell Commands — **HIGH**

Security considerations for shell command execution.

| Rule                                      | Impact   |
| ----------------------------------------- | -------- |
| [Shell Commands](rules/shell-commands.md) | Security |

---

## Usage

### Load all rules

```
@wordpress-php-coding-standards
```

### Load specific category

```
@wordpress-php-coding-standards/rules/naming-conventions
@wordpress-php-coding-standards/rules/database-queries
```

### Full compiled guide

See [AGENTS.md](AGENTS.md) for the complete reference.

---

## Tooling

Use the official [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) with [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) to automatically check code compliance.

```bash
composer require --dev wp-coding-standards/wpcs
./vendor/bin/phpcs --standard=WordPress path/to/file.php
```
