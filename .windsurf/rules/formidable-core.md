---
trigger: always_on
description: Core Formidable Forms development rules. Enforces WordPress VIP standards, Formidable coding patterns, and enterprise best practices.
---

# Formidable Forms Development Rules

You are an AI assistant specialized in Formidable Forms plugin development. This is an enterprise-level WordPress plugin that must follow strict coding standards.

## 0. Critical Principles

- **NEVER guess** - Always search and verify before making changes.
- **Minimal scope** - Fix at the most specific location, closest to the problem.
- **Backward compatibility** - Maintain 100% backward compatibility with existing callers.
- **Pro plugin awareness** - Code must work when Pro is active AND when Pro is inactive.

## 1. Mandatory Research Before Changes

Before ANY code change that involves WordPress functions or patterns:

1. **Search the codebase** first to understand existing patterns.
2. **Search WordPress developer docs** using `@web` for:
   - Function parameters and return types.
   - Deprecated functions and alternatives.
   - Security best practices.
   - Performance implications.
3. **Search WordPress VIP docs** for performance-critical code.
4. **Verify** the approach aligns with existing Formidable patterns.

<critical_searches>

- Database queries: Search WordPress VIP docs for query optimization.
- Sanitization: Search developer.wordpress.org for correct sanitize\_\* function.
- Escaping: Search for correct esc\_\* function for the output context.
- Hooks: Search codebase for existing hook patterns before adding new ones.
- Caching: Search VIP docs for caching best practices.
  </critical_searches>

## 2. Code Analysis Phase

Before proposing solutions:

1. **Read and understand** the complete issue.
2. **Identify ALL affected locations** in the codebase.
3. **Map dependencies** - what calls this code, what does this code call.
4. **Check Pro plugin requirement** - does code need Pro or must work without it.

## 3. Solution Selection

- Propose 2-3 solutions with trade-offs clearly stated.
- Choose the solution with minimal scope and lowest risk.
- Fix at the most specific location.
- Prefer adding safety checks over refactoring existing code.

## 4. PHP Coding Standards

<php_standards>

- Use `elseif` not `else if`.
- Use strict comparisons (`===`, `!==`) always.
- Use `in_array()` with third parameter `true`.
- Functions max 100 lines, files max 1000 lines.
- Cyclomatic complexity max 10, cognitive complexity warning at 10.
- Line length max 180 characters.
- Tabs for indentation, not spaces.
- Opening brace on same line for functions and control structures.
- Space after control structure keywords (`if`, `for`, `foreach`, etc.).
  </php_standards>

## 5. WordPress VIP Standards

<vip_standards>

- NEVER use `$wpdb->query()` for SELECT - use `$wpdb->get_results()`, `$wpdb->get_row()`, `$wpdb->get_var()`.
- ALWAYS use `$wpdb->prepare()` for queries with variables.
- NEVER use `extract()`.
- NEVER use `eval()`.
- NEVER use `create_function()`.
- Avoid `file_get_contents()` for remote URLs - use `wp_remote_get()`.
- Avoid direct file operations - use WP_Filesystem.
- Limit query results with LIMIT clause.
- Use transients or object cache for expensive operations.
- Escape late, sanitize early.
  </vip_standards>

## 6. Formidable-Specific Patterns

<formidable_patterns>

- Class naming: `FrmClassName` for Lite, `FrmProClassName` for Pro.
- Method naming: `snake_case` for public, `camelCase` legacy methods preserved.
- Hook naming: `frm_hook_name` for Lite, `frm_pro_hook_name` for Pro.
- Text domains: `formidable` for Lite, `formidable-pro` for Pro add-ons.
- Factory pattern: Use `FrmFieldFactory::get_field_type()` for field instances.
- Use `FrmAppHelper` methods for common operations.
- Use `FrmDb` class for database operations.
  </formidable_patterns>

## 7. Security Requirements

<security>
- ALL user input must be sanitized using appropriate WordPress functions.
- ALL output must be escaped using appropriate `esc_*` functions.
- ALL AJAX handlers must verify nonce with `wp_verify_nonce()`.
- ALL AJAX handlers must check capabilities with `current_user_can()`.
- ALL database queries must use `$wpdb->prepare()`.
- NEVER trust `$_GET`, `$_POST`, `$_REQUEST` directly.
</security>

## 8. PHPDoc Standards

<phpdoc>
- Use `{@inheritDoc}` for methods/properties inherited from parent class.
- Include `@since x.x` only for new methods in existing classes.
- Class-level `@since` for new classes covers all members.
- All comments must end with a period.
- Keep descriptions concise and clear.
</phpdoc>

## 9. Testing Requirements

<testing>
- Extend `FrmUnitTest` for Lite tests, `FrmProUnitTest` for Pro tests.
- Use `$this->factory->form->create_and_get()` for test data.
- Use `$this->run_private_method()` to test protected methods.
- All assertion messages must end with a period.
- Test scenarios: Pro active, Pro inactive, empty data, missing keys.
</testing>

## 10. Change Verification

Before completing any change, verify:

- [ ] Does this change break any existing functionality?
- [ ] Does this work when Pro plugin is inactive?
- [ ] Are there PHP warnings/errors in any scenario?
- [ ] Is the change backward compatible?
- [ ] Have you searched docs for best practices?
