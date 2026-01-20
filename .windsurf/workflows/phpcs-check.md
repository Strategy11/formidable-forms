---
name: phpcs-check
description: Run PHP CodeSniffer to check code against Formidable and WordPress VIP standards
---

# PHPCS Check

## Steps

1. Determine the target file or directory:
   - If a specific file was mentioned, check that file
   - If a directory was mentioned, check that directory
   - Otherwise, check recently modified files

2. Run PHPCS on Lite plugin:

   ```bash
   cd /Users/sherv/Local\ Sites/formidable/app/public/wp-content/plugins/formidable-master
   vendor/bin/phpcs --standard=phpcs.xml [target]
   ```

3. Run PHPCS on Pro plugin:

   ```bash
   cd /Users/sherv/Local\ Sites/formidable/app/public/wp-content/plugins/formidable-pro-master
   vendor/bin/phpcs --standard=phpcs.xml [target]
   ```

4. For auto-fixing issues:

   ```bash
   vendor/bin/phpcbf --standard=phpcs.xml [target]
   ```

5. Report findings:
   - List errors by severity
   - Explain what each error means
   - Suggest fixes for common issues

## Common PHPCS Errors

| Error                      | Fix                                               |
| -------------------------- | ------------------------------------------------- |
| Missing nonce verification | Add `check_ajax_referer()` or `wp_verify_nonce()` |
| Unescaped output           | Add appropriate `esc_*` function                  |
| Unsanitized input          | Add appropriate `sanitize_*` function             |
| Unprepared SQL query       | Use `$wpdb->prepare()`                            |
| Strict comparison          | Use `===` instead of `==`                         |
