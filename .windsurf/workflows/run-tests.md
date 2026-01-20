---
name: run-tests
description: Run PHPUnit tests for Formidable Forms plugins
---

# Run Formidable Tests

## Steps

1. Determine which plugin to test based on the current context:
   - If working in `formidable-master` → Run Lite tests
   - If working in `formidable-pro-master` → Run Pro tests
   - If working in an add-on → Run add-on tests

2. Check if PHPUnit is available:

   ```bash
   vendor/bin/phpunit --version
   ```

3. Run the appropriate tests:

   **For Lite plugin:**

   ```bash
   cd /Users/sherv/Local\ Sites/formidable/app/public/wp-content/plugins/formidable-master
   vendor/bin/phpunit
   ```

   **For Pro plugin:**

   ```bash
   cd /Users/sherv/Local\ Sites/formidable/app/public/wp-content/plugins/formidable-pro-master
   vendor/bin/phpunit
   ```

4. If a specific test file was modified, run only that test:

   ```bash
   vendor/bin/phpunit tests/phpunit/path/to/test_file.php
   ```

5. If a specific test method needs to run:

   ```bash
   vendor/bin/phpunit --filter test_method_name
   ```

6. Report the results:
   - Number of tests passed/failed
   - Any error messages
   - Suggestions for fixing failures
