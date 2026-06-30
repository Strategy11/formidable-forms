---
name: eslint-rules
description: Creating and maintaining custom ESLint rules for Formidable Forms. Use when adding new custom ESLint rules, modifying existing ones, or debugging rule behavior.
---

# Custom ESLint Rules

Workflow for creating and maintaining custom ESLint rules in the Formidable Forms plugin.

## When to Use

- Adding a new custom ESLint rule
- Modifying an existing custom rule
- Debugging why a custom rule is not catching a pattern
- Running ESLint with custom rules

---

## Architecture

Custom ESLint rules live in `/eslint-rules/` at the project root.

```text
eslint-rules/
├── index.js          # Plugin entry point, exports all rules
└── rules/            # Individual rule files
    ├── prefer-strict-comparison.js
    ├── no-redundant-undefined-check.js
    ├── prefer-includes.js
    ├── no-typeof-undefined.js
    ├── no-optional-chaining-queryselectorall.js
    ├── no-repeated-selector.js
    └── prefer-document-fragment.js
```

The plugin is imported in `eslint.config.mjs` as `formidable` and rules are referenced as `formidable/<rule-name>`.

### Release Exclusions

The `/eslint-rules/` directory is excluded from releases via:

- `.gitattributes`: `export-ignore`
- `bin/zip-plugin.sh`: `-x "*/eslint-rules/*"`

This mirrors the pattern used for `/phpcs-sniffs/`.

---

## Existing Rules

### formidable/prefer-strict-comparison

Enforces `===` and `!==` instead of `==` and `!=` when comparing against non-empty, non-numeric string literals. Mirrors the PHP sniff `PreferStrictComparisonSniff`.

- **Fixable:** Yes
- **Safe strings:** Non-empty and non-numeric (e.g., `'string'`, `'post'`)
- **Unsafe strings (skipped):** `''`, `'0'`, `'123'`, `'1.5'`

### formidable/no-redundant-undefined-check

Detects `x !== undefined && x` patterns where the undefined check is redundant because the truthy check already covers it.

- **Fixable:** Yes
- **Pattern:** `expr !== undefined && expr` becomes just `expr`

### formidable/prefer-includes

Detects `.indexOf()` comparisons with `-1` and suggests `.includes()` instead. Catches yoda-style patterns that `unicorn/prefer-includes` misses (e.g., `-1 !== [].indexOf(x)`).

- **Fixable:** Yes
- **Patterns caught:**
    - `arr.indexOf(x) !== -1` and yoda `-1 !== arr.indexOf(x)`
    - `arr.indexOf(x) === -1` and yoda `-1 === arr.indexOf(x)`
    - `arr.indexOf(x) > -1` and yoda `-1 < arr.indexOf(x)`
    - `arr.indexOf(x) >= 0`

### formidable/no-typeof-undefined

Detects `typeof x === 'undefined'` and yoda `'undefined' === typeof x` patterns. Replaces with direct `x === undefined` comparison. Catches yoda-style patterns that `unicorn/no-typeof-undefined` misses.

- **Fixable:** Yes
- **Patterns caught:**
    - `typeof x === 'undefined'` / `typeof x == 'undefined'`
    - `'undefined' === typeof x` / `'undefined' == typeof x` (yoda)
    - Both `===`/`!==` and `==`/`!=` variants

### formidable/no-optional-chaining-queryselectorall

Prevents unnecessary optional chaining (`?.`) on DOM methods that always return a value (never null/undefined). Methods like `querySelectorAll`, `getElementsByClassName`, and `children` always return a collection, so optional chaining is redundant.

- **Fixable:** Yes
- **Severity:** Error
- **Patterns caught:**
    - `document.querySelectorAll(...)?.forEach`
    - `element.getElementsByClassName(...)?.[0]`
    - `element.children?.length`

### formidable/no-repeated-selector

Detects repeated calls to `querySelector` or `querySelectorAll` with the same selector string in the same function scope. Suggests caching the result in a variable.

- **Fixable:** No (requires developer judgment)
- **Severity:** Warning
- **Example:** Multiple `document.querySelector('.item')` calls should be cached

### formidable/prefer-document-fragment

Detects `appendChild`, `append`, or `prepend` calls inside loops. Suggests using `DocumentFragment` to batch DOM operations and prevent multiple reflows.

- **Fixable:** No (requires restructuring code)
- **Severity:** Warning
- **Loop types detected:** `for`, `for...of`, `for...in`, `while`, `forEach`, `map`

---

## Adding a New Rule

### Step 1: Create the Rule File

Create a new file in `eslint-rules/rules/<rule-name>.js`. Follow the existing pattern:

```javascript
'use strict';

module.exports = {
    meta: {
        type: 'suggestion',           // 'suggestion', 'problem', or 'layout'
        docs: {
            description: 'Description of what the rule enforces.',
        },
        fixable: 'code',              // 'code' if auto-fixable, null otherwise
        schema: [],                    // JSON Schema for rule options
        messages: {
            messageId: 'Error message with {{placeholder}}.',
        },
    },

    create( context ) {
        const sourceCode = context.sourceCode;

        return {
            // AST node visitor(s)
            BinaryExpression( node ) {
                // Rule logic
                context.report({
                    node,
                    messageId: 'messageId',
                    data: { placeholder: 'value' },
                    fix( fixer ) {
                        return fixer.replaceText( node, 'replacement' );
                    },
                });
            },
        };
    },
};
```

### Step 2: Register the Rule

Add the rule to `eslint-rules/index.js`:

```javascript
const newRule = require( './rules/new-rule' );

module.exports = {
    rules: {
        // ... existing rules
        'new-rule': newRule,
    },
};
```

### Step 3: Enable in Config

Add to the rules section in `eslint.config.mjs`:

```javascript
'formidable/new-rule': 'error',
```

### Step 4: Verify

```bash
# Check for violations (requires nvm for node)
export PATH="$HOME/.nvm/versions/node/v20.19.2/bin:$PATH"
./node_modules/.bin/eslint .

# Auto-fix all violations
./node_modules/.bin/eslint . --fix
```

Or use the npm scripts:

```bash
npm run lint
npm run lint:fix
```

---

## Design Principles

1. **All rules must support `--fix`** so they can be applied to the existing codebase automatically
2. **Handle yoda-style comparisons** since the WordPress coding standard historically used yoda conditions
3. **Only enforce safe transformations** (e.g., prefer-strict-comparison skips empty and numeric strings)
4. **Complement existing plugins** by catching patterns that unicorn, sonarjs, etc. miss
5. **Mirror PHP sniffs where applicable** to maintain consistency between PHP and JS linting (see `/phpcs-sniffs/`)

---

## AST Explorer

Use <https://astexplorer.net/> with the `espree` parser to inspect AST node types when developing rules. This helps identify the correct node visitors and property names.

## Invocation

Cascade automatically invokes this skill when your request involves custom ESLint rules.

To manually invoke:

```text
@eslint-rules
```
