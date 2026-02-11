---
trigger: glob
globs: ["**/*.php"]
description: Formidable Forms PHP-specific patterns and conventions. Auto-applies to PHP files.
---

# Formidable Forms PHP Patterns

PHP-specific patterns, coding standards, and architectural decisions for Formidable Forms.

---

## PHP Version Requirement

Target **PHP 7.0** as the minimum version. Write code using PHP 7.0 features and **do NOT use PHP 7.1+ features**.

### PHP 7.0 Features (Allowed)

- **Scalar type declarations**: `string`, `int`, `float`, `bool` for parameters
- **Return type declarations**: Specify return types after `)`
- **Null coalescing operator**: `$value = $input ?? 'default';`
- **Spaceship operator**: `$a <=> $b` returns -1, 0, or 1
- **Constant arrays with define()**: `define( 'ITEMS', array( 'a', 'b' ) );`
- **Anonymous classes**: `new class { ... }`
- **Generator return expressions**: Generators can return values
- **Integer division**: `intdiv( 10, 3 )`
- **CSPRNG functions**: `random_bytes()`, `random_int()`

### PHP 7.1+ Features (NOT Allowed)

| Feature                            | Version | Why Avoid              |
| ---------------------------------- | ------- | ---------------------- |
| Nullable types (`?string`)         | 7.1     | Syntax not available   |
| `void` return type                 | 7.1     | Type not available     |
| `iterable` pseudo-type             | 7.1     | Type not available     |
| Class constant visibility          | 7.1     | Syntax not available   |
| Multi-catch exceptions (`\|`)      | 7.1     | Syntax not available   |
| Symmetric array destructuring      | 7.1     | Syntax not available   |
| `object` type hint                 | 7.2     | Type not available     |
| Arrow functions (`fn()`)           | 7.4     | Syntax not available   |
| Typed properties                   | 7.4     | Syntax not available   |
| Null coalescing assignment (`??=`) | 7.4     | Operator not available |
| Spread operator in arrays          | 7.4     | Syntax not available   |
| Null safe operator (`?->`)         | 8.0     | Operator not available |
| Named arguments                    | 8.0     | Syntax not available   |
| Match expressions                  | 8.0     | Syntax not available   |
| Constructor property promotion     | 8.0     | Syntax not available   |
| Union types                        | 8.0     | Syntax not available   |
| Attributes                         | 8.0     | Syntax not available   |

---

## WordPress Version Requirement

Target **WordPress 5.5** as the minimum version.

### Version Compatibility

When using newer WordPress functions or features:

1. **Check version before use**: Use `version_compare()` for WP version checks
2. **Provide fallbacks**: Implement backward-compatible alternatives
3. **Document requirements**: Note minimum versions in docblocks

```php
// Example: Using a newer function with fallback
if ( version_compare( get_bloginfo( 'version' ), '5.9', '>=' ) ) {
    // Use WordPress 5.9+ feature
    $result = wp_new_function();
} else {
    // Fallback for older versions
    $result = frm_legacy_fallback();
}
```

---

## Class Naming

- **Lite plugin:** `FrmClassName` (e.g., `FrmAppHelper`, `FrmDb`, `FrmField`)
- **Pro plugin:** `FrmProClassName` (e.g., `FrmProAppHelper`, `FrmProField`)

---

## Hook Naming

- **Lite hooks:** `frm_hook_name`
- **Pro hooks:** `frm_pro_hook_name`
- **Addons hooks:** `frm_addon_hook_name`

---

## PHPCS Sniffs Rules

Formidable has custom PHP_CodeSniffer sniffs in `phpcs-sniffs/Formidable/`. **Always read these sniffs before making changes** as they are continuously updated.

### Whitespace Rules

| Sniff                             | Description                              |
| --------------------------------- | ---------------------------------------- |
| `BlankLineAfterClosingBrace`      | Add blank line after closing brace       |
| `BlankLineBeforeReturnAfterBrace` | Add blank line before return after brace |
| `ConsecutiveAssignmentSpacing`    | Align consecutive assignments            |
| `NoBlankLineAfterLoopOpen`        | No blank line after loop opening         |
| `NoBlankLineAfterIfOpen`          | No blank line after if opening           |
| `NoBlankLineAfterFunctionOpen`    | No blank line after function opening     |
| `NoBlankLineBeforeCloseBrace`     | No blank line before closing brace       |
| `NoBlankLineInShortIf`            | No blank lines in short if statements    |
| `ShortFunctionBlankLine`          | Proper blank lines in short functions    |

### Code Analysis Rules

| Sniff                                | Description                                             |
| ------------------------------------ | ------------------------------------------------------- |
| `FlipIfToEarlyReturn`                | Convert if blocks to early returns                      |
| `FlipIfElseToEarlyReturn`            | Convert if/else to early return pattern                 |
| `FlipForeachIfToContinue`            | Convert foreach if to continue                          |
| `FlipNegativeTernary`                | Flip negative ternary conditions                        |
| `PreferArrayKeyExists`               | Use `array_key_exists()` over `isset()` for null values |
| `PreferEmptyArrayComparison`         | Use `=== array()` over `empty()` for arrays             |
| `PreferStrictComparison`             | Use `===` and `!==` over `==` and `!=`                  |
| `PreferStrictInArray`                | Use strict mode in `in_array()`                         |
| `PreferStrcasecmp`                   | Use `strcasecmp()` for case-insensitive comparison      |
| `MoveSimpleCheckBeforeExpensiveCall` | Reorder conditions for performance                      |
| `SimplifyIfReturn`                   | Simplify if statements that only return                 |
| `SimplifyEmptyTernary`               | Simplify redundant empty ternaries                      |
| `RedundantEmptyAfterTypeCheck`       | Remove redundant empty after type check                 |
| `RedundantIssetBeforeNotEmpty`       | Remove redundant isset before !empty                    |
| `RedundantParentheses`               | Remove unnecessary parentheses                          |

### Security Rules

| Sniff                      | Description                                   |
| -------------------------- | --------------------------------------------- |
| `AddDirectFileAccessCheck` | Add direct file access prevention             |
| `BreakEchoConcatenation`   | Break echo concatenation for escaping         |
| `EscapeInHtml`             | Ensure proper escaping in HTML output         |
| `PreferWpSafeRedirect`     | Use `wp_safe_redirect()` over `wp_redirect()` |

### Commenting Rules

| Sniff                    | Description                            |
| ------------------------ | -------------------------------------- |
| `AddBoolReturnTag`       | Add `@return bool` tag when applicable |
| `AddMissingDocblock`     | Add missing docblocks                  |
| `AddMissingParamType`    | Add missing `@param` types             |
| `FixIncorrectReturnType` | Fix incorrect `@return` types          |
| `CommentSpacing`         | Ensure proper comment spacing          |

### PHPUnit Rules

| Sniff                        | Description                        |
| ---------------------------- | ---------------------------------- |
| `PreferAssertIsArray`        | Use `assertIsArray()`              |
| `PreferAssertArrayHasKey`    | Use `assertArrayHasKey()`          |
| `PreferAssertStringContains` | Use `assertStringContainsString()` |
| `PreferAssertContains`       | Use `assertContains()`             |
| `PreferAssertFileExists`     | Use `assertFileExists()`           |

### Future-Proofing

Before making code changes:

1. **Read the ruleset**: Check `phpcs-sniffs/Formidable/ruleset.xml` for current rules
2. **Check individual sniffs**: Browse `phpcs-sniffs/Formidable/Sniffs/` for detailed implementations
3. **Stay updated**: Sniffs are continuously updated, always verify current rules
