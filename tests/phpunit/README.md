# Running Lite tests

Use the WordPress develop test environment and a dedicated test database. Set `WP_DEVELOP_DIR` to the checkout directory, including the trailing slash. The checkout must load Lite under `src/wp-content/plugins/formidable`.

PHPUnit 11 and 12 use the default configuration:

```sh
WP_DEVELOP_DIR=/tmp/wordpress/ vendor/bin/phpunit
```

PHPUnit 9 uses the legacy configuration:

```sh
WP_DEVELOP_DIR=/tmp/wordpress/ vendor/bin/phpunit -c phpunit9.xml
```

Use PHPUnit 9 on PHP 7.4/8.0, PHPUnit 11 on PHP 8.2 or newer, and PHPUnit 12 on PHP 8.3 or newer. Polyfills 4 does not support PHPUnit 10.

Keep the PHPUnit 9 annotations alongside attributes while PHPUnit 9 remains supported. Data providers must be public and static. Coverage metadata is declared on test classes for both runners because current PHPUnit does not allow coverage attributes on test methods.

`FrmPHPUnitCompatibility` overrides WordPress's deprecated annotation parser in both Lite test base classes. It keeps WordPress's deprecation collectors and assertions, including `@expectedDeprecated`, `@expectedIncorrectUsage`, and programmatic expectations. No changes to WordPress are needed.

`phpunit-attributes-stubs.php` supplies metadata declarations to Mago jobs that do not install PHPUnit. It is an analysis include and must never be loaded by the test bootstrap.
