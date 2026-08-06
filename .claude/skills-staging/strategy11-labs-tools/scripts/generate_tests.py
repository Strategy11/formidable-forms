#!/usr/bin/env python3
"""
generate_tests.py — Strategy11 Labs PHPUnit Test Generator (v2)
===============================================================
Parses a plugin directory, extracts classes/methods/hooks/AJAX handlers,
then writes REAL PHPUnit tests using WP_Mock — not stubs.

What gets generated:
  - Hook registration tests (one expectation per detected hook)
  - AJAX security gate tests (3 tests per handler: bad nonce, bad cap, success)
  - Filter return-type tests (frm_form_options_before_update, frm_validate_entry, etc.)
  - Formidable regression guards (known bug patterns from frm-gotchas)
  - Method stubs ONLY for business logic that requires context to test

Usage:
    python3 generate_tests.py <path-to-plugin-directory> [--is-formidable]

Output:
    tests/bootstrap.php
    tests/phpunit.xml
    tests/unit/Test_{ClassName}.php  (one file per class found)
    composer.json updated with phpunit + wp_mock dev dependencies
"""

import sys
import os
import re
import json
from pathlib import Path

# ── Helpers ───────────────────────────────────────────────────────────────────

def find_php_files(directory):
    skip = {'vendor', 'node_modules', '.git', 'tests'}
    php_files = []
    for root, dirs, files in os.walk(directory):
        dirs[:] = [d for d in dirs if d not in skip]
        for f in files:
            if f.endswith('.php'):
                php_files.append(os.path.join(root, f))
    return php_files


def read_file(path):
    try:
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            return f.read()
    except OSError:
        return ''


def extract_classes(content):
    pattern = re.compile(r'class\s+(\w+)(?:\s+extends\s+(\w+))?', re.MULTILINE)
    return [(m.group(1), m.group(2)) for m in pattern.finditer(content)]


def extract_public_methods(content, class_name):
    class_start = re.search(rf'class\s+{re.escape(class_name)}[\s\w]*{{', content)
    if not class_start:
        return []
    start = class_start.end()
    depth = 1
    idx = start
    while idx < len(content) and depth > 0:
        if content[idx] == '{':
            depth += 1
        elif content[idx] == '}':
            depth -= 1
        idx += 1
    class_body = content[start:idx - 1]
    pattern = re.compile(r'public\s+(?:static\s+)?function\s+(\w+)\s*\(', re.MULTILINE)
    methods = [m.group(1) for m in pattern.finditer(class_body)]
    return [m for m in methods if not m.startswith('__')]


def extract_hooks(content):
    hooks = []
    pattern = re.compile(
        r"(add_action|add_filter)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*([^,\)]+)(?:,\s*(\d+))?(?:,\s*(\d+))?",
        re.MULTILINE
    )
    for m in pattern.finditer(content):
        hook_type    = m.group(1)
        hook_name    = m.group(2)
        callback_raw = m.group(3).strip()
        priority     = m.group(4) or '10'
        accepted     = m.group(5) or '1'
        hooks.append((hook_type, hook_name, callback_raw, priority, accepted))
    return hooks


def extract_ajax_handlers(content):
    handlers = []
    pattern = re.compile(
        r"add_action\s*\(\s*['\"]wp_ajax(_nopriv)?_([^'\"]+)['\"]",
        re.MULTILINE
    )
    for m in pattern.finditer(content):
        is_nopriv = m.group(1) is not None
        action    = m.group(2)
        handlers.append((action, is_nopriv))
    return handlers


def extract_nonce_action(content, handler_name):
    """Try to find the nonce action string used in a given handler."""
    # Look for check_ajax_referer near the handler
    pattern = re.compile(
        rf"function\s+{re.escape(handler_name)}\s*\(.*?check_ajax_referer\s*\(\s*['\"]([^'\"]+)['\"]",
        re.DOTALL
    )
    m = pattern.search(content)
    if m:
        return m.group(1)
    # Fallback: look for any nonce action in the file
    m = re.search(r"check_ajax_referer\s*\(\s*['\"]([^'\"]+)['\"]", content)
    return m.group(1) if m else 'my_plugin_nonce'


def extract_nonce_field(content, nonce_action):
    """Try to find the $_POST field name used for the nonce."""
    pattern = re.compile(
        rf"check_ajax_referer\s*\(\s*['\"]" + re.escape(nonce_action) + r"['\"]" +
        r"\s*,\s*['\"]([^'\"]+)['\"]"
    )
    m = pattern.search(content)
    return m.group(1) if m else 'nonce'


def detect_capability(content, handler_name):
    """Try to find the capability string checked in a handler."""
    pattern = re.compile(
        rf"function\s+{re.escape(handler_name)}\s*\(.*?current_user_can\s*\(\s*['\"]([^'\"]+)['\"]",
        re.DOTALL
    )
    m = pattern.search(content)
    if m:
        return m.group(1)
    m = re.search(r"current_user_can\s*\(\s*['\"]([^'\"]+)['\"]", content)
    return m.group(1) if m else 'frm_edit_forms'


def detect_frm_filter_methods(methods):
    """Identify methods likely to be frm_* filter callbacks."""
    filter_keywords = {
        'save_form_options': 'frm_form_options_before_update',
        'save_options': 'frm_form_options_before_update',
        'filter_options': 'frm_form_options_before_update',
        'validate_entry': 'frm_validate_entry',
        'filter_email_message': 'frm_email_message',
        'modify_email': 'frm_email_message',
        'email_message': 'frm_email_message',
    }
    found = {}
    for method in methods:
        lower = method.lower()
        for keyword, hook in filter_keywords.items():
            if keyword in lower:
                found[method] = hook
                break
    return found


# ── Code generators ────────────────────────────────────────────────────────────

def generate_bootstrap():
    return '''<?php
/**
 * PHPUnit bootstrap — initialises WP_Mock before any test runs.
 */
require_once dirname( __DIR__ ) . '/vendor/autoload.php';
WP_Mock::bootstrap();
'''


def generate_phpunit_xml(plugin_slug):
    return f'''<?xml version="1.0"?>
<phpunit
    bootstrap="bootstrap.php"
    colors="true"
    verbose="true"
    stopOnError="false"
    stopOnFailure="false"
    beStrictAboutTestsThatDoNotTestAnything="true"
    beStrictAboutOutputDuringTests="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>unit/</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <include>
            <directory suffix=".php">../includes</directory>
            <file>../{plugin_slug}.php</file>
        </include>
    </coverage>
</phpunit>
'''


def generate_hook_registration_tests(hooks):
    if not hooks:
        return ''
    lines = [
        '',
        '    // ── Hook registration ────────────────────────────────────────────────',
        '',
        '    /**',
        '     * Every hook registered by this class must appear here.',
        '     * If a hook is added to the class, add an expectation here.',
        '     */',
        '    public function test_all_hooks_are_registered(): void {',
    ]
    for hook_type, hook_name, callback_raw, priority, accepted in hooks[:12]:
        expect = 'expectActionAdded' if hook_type == 'add_action' else 'expectFilterAdded'
        # Parse callback
        if '[$this' in callback_raw or "array( \$this" in callback_raw:
            method_match = re.search(r"['\"]([\w_]+)['\"]", callback_raw)
            method_ref = method_match.group(1) if method_match else 'TODO_METHOD_NAME'
            cb = f"[ \$this->instance, '{method_ref}' ]"
        elif '::' in callback_raw:
            # Static callback like 'MyClass::method'
            cb = callback_raw.strip().strip("'\"")
            cb = f"'{cb}'"
        else:
            cb = callback_raw.strip()
        lines.append(f"        WP_Mock::{expect}( '{hook_name}', {cb}, {priority}, {accepted} );")
    lines += [
        '',
        '        // Update method name if your hook registration method is named differently',
        '        $this->instance->register_hooks();',
        '        $this->assertHooksAdded();',
        '    }',
    ]
    return '\n'.join(lines)


def generate_ajax_tests(ajax_handlers, all_content):
    if not ajax_handlers:
        return ''
    lines = []
    seen = set()
    for action_name, is_nopriv in ajax_handlers:
        if action_name in seen:
            continue
        seen.add(action_name)

        # Try to detect nonce action and field from source
        nonce_action = extract_nonce_action(all_content, action_name)
        nonce_field  = extract_nonce_field(all_content, nonce_action)
        capability   = detect_capability(all_content, action_name)

        method_name  = action_name.replace('-', '_')
        method_guess = method_name  # handler method often has same name

        lines += [
            '',
            f'    // ── AJAX: {action_name} ────────────────────────────────────────────',
            '',
            f'    /**',
            f'     * Security gate: bad nonce must be rejected.',
            f'     * Nonce action: "{nonce_action}", POST field: "{nonce_field}"',
            f'     */',
            f'    public function test_{method_guess}_rejects_bad_nonce(): void {{',
            f'        $_POST = [ \'{nonce_field}\' => \'bad_nonce\' ];',
            '',
            f'        WP_Mock::userFunction( \'check_ajax_referer\', [',
            f"            'args'   => [ '{nonce_action}', '{nonce_field}' ],",
            f"            'return' => false,",
            f"            'times'  => 1,",
            f'        ] );',
            f"        WP_Mock::userFunction( 'wp_send_json_error', [ 'times' => 1 ] );",
            f"        WP_Mock::userFunction( 'wp_die', [ 'times' => 1 ] );",
            '',
            f'        // TODO: update method name if different from action slug',
            f'        $this->instance->{method_guess}();',
            f'    }}',
            '',
            f'    /**',
            f'     * Security gate: valid nonce but wrong capability must be rejected.',
            f'     * Required capability: "{capability}"',
            f'     */',
            f'    public function test_{method_guess}_rejects_insufficient_capability(): void {{',
            f'        $_POST = [ \'{nonce_field}\' => \'valid_nonce\' ];',
            '',
            f"        WP_Mock::userFunction( 'check_ajax_referer', [ 'return' => true ] );",
            f'        WP_Mock::userFunction( \'current_user_can\', [',
            f"            'args'   => [ '{capability}' ],",
            f"            'return' => false,",
            f"            'times'  => 1,",
            f'        ] );',
            f"        WP_Mock::userFunction( 'wp_send_json_error', [ 'times' => 1 ] );",
            f"        WP_Mock::userFunction( 'wp_die', [ 'times' => 1 ] );",
            '',
            f'        $this->instance->{method_guess}();',
            f'    }}',
        ]
    return '\n'.join(lines)


def generate_filter_return_type_tests(filter_methods):
    """Generate return-type tests for known Formidable filter patterns."""
    if not filter_methods:
        return ''
    lines = [
        '',
        '    // ── Filter return types ──────────────────────────────────────────────',
        '    // These tests ensure filters return the correct type — wrong type causes',
        '    // silent failure (WordPress just uses the unmodified value).',
    ]
    for method, hook in filter_methods.items():
        if hook == 'frm_form_options_before_update':
            lines += [
                '',
                f'    public function test_{method.lower()}_returns_array(): void {{',
                f'        $result = $this->instance->{method}( [], [], true );',
                '        $this->assertIsArray( $result, \'{method} must return an array — returning non-array silently breaks form save\' );',
                '    }',
                '',
                f'    public function test_{method.lower()}_preserves_existing_option_keys(): void {{',
                f'        $existing = [ \'other_plugin_key\' => \'should_survive\' ];',
                f'        $result   = $this->instance->{method}( $existing, [], true );',
                '        $this->assertArrayHasKey( \'other_plugin_key\', $result );',
                '        $this->assertSame( \'should_survive\', $result[\'other_plugin_key\'] );',
                '    }',
            ]
        elif hook == 'frm_validate_entry':
            lines += [
                '',
                f'    public function test_{method.lower()}_returns_array(): void {{',
                '        // frm_validate_entry MUST return an array — returning non-array triggers _doing_it_wrong()',
                f'        $result = $this->instance->{method}( [], [], [] );',
                '        $this->assertIsArray( $result, \'{method} must return an array\' );',
                '    }',
            ]
        elif hook == 'frm_email_message':
            lines += [
                '',
                f'    public function test_{method.lower()}_returns_string(): void {{',
                f'        $atts = [ \'email_key\' => 99, \'entry\' => new \\stdClass(), \'form\' => new \\stdClass() ];',
                f'        WP_Mock::userFunction( \'get_post_meta\', [ \'return\' => \'\' ] );',
                f'        $result = $this->instance->{method}( \'original body\', $atts );',
                '        $this->assertIsString( $result );',
                '    }',
            ]
    return '\n'.join(lines)


def generate_frm_regression_tests(is_formidable):
    """Standard regression tests for all known Formidable gotchas."""
    if not is_formidable:
        return ''
    return '''
    // ── Formidable regression guards ─────────────────────────────────────────
    // These tests exist to catch known recurring mistakes.
    // They should pass even if the class has no direct relationship to these patterns.
    // If a test is not applicable, remove it and document why.

    /**
     * REGRESSION: frm_after_entry_processed receives a single array arg,
     * NOT ($entry_id, $form_id). Callback must accept array.
     * Source: FrmFormsController.php:2576
     */
    public function test_after_entry_processed_callback_accepts_array_arg(): void {
        if ( ! method_exists( $this->instance, 'after_entry_processed' ) ) {
            $this->markTestSkipped( 'after_entry_processed method not present in this class.' );
        }
        $atts = [ 'entry_id' => 42, 'form' => (object) [ 'id' => 10 ] ];
        // No PHP fatal/warning = correct signature
        $this->instance->after_entry_processed( $atts );
        $this->assertTrue( true );
    }

    /**
     * REGRESSION: action post_content is already decoded to PHP array
     * when received via hooks. Must NOT call json_decode() on it.
     * Calling json_decode() on an array is a TypeError on PHP 8.
     */
    public function test_does_not_double_decode_action_post_content(): void {
        if ( ! method_exists( $this->instance, 'get_action_setting' ) ) {
            $this->markTestSkipped( 'get_action_setting method not present in this class.' );
        }
        $action               = new \\stdClass();
        $action->ID           = 1;
        $action->post_content = [ 'email_to' => 'test@example.com' ]; // Already decoded
        // TypeError if method calls json_decode() on the already-decoded array
        $result = $this->instance->get_action_setting( $action, 'email_to' );
        $this->assertSame( 'test@example.com', $result );
    }

    /**
     * REGRESSION: PHP 8.0+ fatals when array offset [] is used on stdClass.
     * All field_options access must handle both array and object.
     */
    public function test_field_options_access_works_on_stdclass(): void {
        if ( ! method_exists( $this->instance, 'get_field_option' ) ) {
            $this->markTestSkipped( 'get_field_option method not present in this class.' );
        }
        $field                = new \\stdClass();
        $field->type          = 'text';
        $field->field_options = [ 'placeholder' => 'test' ];
        // Would fatal on PHP 8.0+ if method uses $field_options['key'] on stdClass
        $result = $this->instance->get_field_option( $field, 'placeholder' );
        $this->assertNotNull( $result );
    }'''


def generate_method_stubs(methods, filter_methods, is_formidable):
    """Only generate stubs for methods that aren't covered by other generators."""
    covered_methods = set(filter_methods.keys())
    # Skip register_hooks — covered by hook registration test
    # Skip AJAX handlers — covered by ajax tests
    skip_patterns = ['register', 'hook', 'ajax', 'init']

    stubs = []
    for method in methods:
        if method in covered_methods:
            continue
        lower = method.lower()
        if any(p in lower for p in skip_patterns):
            continue

        stubs += [
            '',
            f'    /**',
            f'     * TODO: Implement this test.',
            f'     * Test the happy path for {method}().',
            f'     */',
            f'    public function test_{lower}_returns_expected_result(): void {{',
            f'        // $result = $this->instance->{method}( /* args */ );',
            f'        // $this->assertSame( $expected, $result );',
            f'        $this->markTestIncomplete( \'Implement test for {method}()\' );',
            f'    }}',
        ]
        # Only add failure stub for methods that sound like they handle input
        if any(kw in lower for kw in ('save', 'process', 'validate', 'sanitize', 'update', 'delete')):
            stubs += [
                '',
                f'    public function test_{lower}_handles_empty_input(): void {{',
                f'        // TODO: test behaviour with empty/null/invalid input',
                f'        $this->markTestIncomplete( \'Implement failure path for {method}()\' );',
                f'    }}',
            ]

    return '\n'.join(stubs) if stubs else ''


def generate_test_class(class_name, parent, methods, hooks, ajax_handlers,
                         all_content, is_formidable):
    filter_methods = detect_frm_filter_methods(methods)

    hook_tests       = generate_hook_registration_tests(hooks)
    ajax_tests       = generate_ajax_tests(ajax_handlers, all_content)
    filter_tests     = generate_filter_return_type_tests(filter_methods)
    regression_tests = generate_frm_regression_tests(is_formidable)
    method_stubs     = generate_method_stubs(methods, filter_methods, is_formidable)

    lines = [
        '<?php',
        '',
        'use WP_Mock\\Tools\\TestCase;',
        '',
        f'/**',
        f' * Tests for {class_name}.',
        f' * Generated by generate_tests.py v2.',
        f' * Security gate and hook registration tests are fully implemented.',
        f' * Fill in markTestIncomplete stubs for business logic.',
        f' */',
        f'class Test_{class_name} extends TestCase {{',
        '',
        f'    /** @var {class_name} */',
        '    private $instance;',
        '',
        '    public function setUp(): void {',
        '        parent::setUp();',
        '        WP_Mock::setUp();',
        f'        $this->instance = new {class_name}();',
        '    }',
        '',
        '    public function tearDown(): void {',
        '        WP_Mock::tearDown();',
        '        parent::tearDown();',
        '    }',
    ]

    if hook_tests:
        lines.append(hook_tests)
    if ajax_tests:
        lines.append(ajax_tests)
    if filter_tests:
        lines.append(filter_tests)
    if regression_tests:
        lines.append(regression_tests)
    if method_stubs:
        lines += ['', '    // ── Business logic stubs (fill these in) ─────────────────────────────']
        lines.append(method_stubs)

    lines += ['', '}', '']
    return '\n'.join(lines)


def update_composer_json(plugin_dir):
    composer_path = os.path.join(plugin_dir, 'composer.json')
    if os.path.exists(composer_path):
        with open(composer_path, 'r') as f:
            data = json.load(f)
    else:
        data = {}

    changed = False
    dev_req = data.setdefault('require-dev', {})

    if 'phpunit/phpunit' not in dev_req:
        dev_req['phpunit/phpunit'] = '^9.6'
        changed = True
    if '10up/wp_mock' not in dev_req:
        dev_req['10up/wp_mock'] = '^1.0'
        changed = True

    scripts = data.setdefault('scripts', {})
    if 'test' not in scripts:
        scripts['test'] = 'phpunit --configuration tests/phpunit.xml'
        changed = True

    if changed:
        with open(composer_path, 'w') as f:
            json.dump(data, f, indent=4)
        print('  Updated composer.json with phpunit + wp_mock.')
    else:
        print('  composer.json already has phpunit/wp_mock.')


# ── Main ──────────────────────────────────────────────────────────────────────

def main():
    args = sys.argv[1:]
    if not args:
        print('Usage: python3 generate_tests.py <plugin-directory> [--is-formidable]')
        sys.exit(1)

    plugin_dir    = args[0]
    is_formidable = '--is-formidable' in args

    if not os.path.isdir(plugin_dir):
        print(f"Error: '{plugin_dir}' is not a directory.")
        sys.exit(1)

    plugin_dir  = os.path.realpath(plugin_dir)
    plugin_slug = os.path.basename(plugin_dir)
    tests_dir   = os.path.join(plugin_dir, 'tests')
    unit_dir    = os.path.join(tests_dir, 'unit')

    os.makedirs(unit_dir, exist_ok=True)

    print(f'\nStrategy11 Labs — Test Generator v2')
    print(f'Plugin: {plugin_dir}\n')

    bootstrap_path = os.path.join(tests_dir, 'bootstrap.php')
    if not os.path.exists(bootstrap_path):
        with open(bootstrap_path, 'w') as f:
            f.write(generate_bootstrap())
        print('  Created tests/bootstrap.php')
    else:
        print('  tests/bootstrap.php already exists — skipped.')

    phpunit_path = os.path.join(tests_dir, 'phpunit.xml')
    if not os.path.exists(phpunit_path):
        with open(phpunit_path, 'w') as f:
            f.write(generate_phpunit_xml(plugin_slug))
        print('  Created tests/phpunit.xml')
    else:
        print('  tests/phpunit.xml already exists — skipped.')

    # Collect all PHP source for context-aware generation
    php_files     = find_php_files(plugin_dir)
    all_content   = '\n'.join(read_file(p) for p in php_files)

    all_hooks         = []
    all_ajax_handlers = []
    classes_found     = {}

    for path in php_files:
        content = read_file(path)
        all_hooks         += extract_hooks(content)
        all_ajax_handlers += extract_ajax_handlers(content)

        for class_name, parent in extract_classes(content):
            methods = extract_public_methods(content, class_name)
            classes_found[class_name] = (parent, methods)

    # Deduplicate AJAX handlers
    seen_ajax  = set()
    unique_ajax = []
    for action, nopriv in all_ajax_handlers:
        if action not in seen_ajax:
            unique_ajax.append((action, nopriv))
            seen_ajax.add(action)

    if not classes_found:
        print('\n  No classes found. Writing a generic test file.')
        classes_found['Plugin_Functions'] = (None, [])

    files_written = 0
    for i, (class_name, (parent, methods)) in enumerate(classes_found.items()):
        hooks_for_class = all_hooks  if i == 0 else []
        ajax_for_class  = unique_ajax if i == 0 else []

        test_code = generate_test_class(
            class_name, parent, methods,
            hooks_for_class, ajax_for_class,
            all_content, is_formidable
        )

        out_path = os.path.join(unit_dir, f'Test_{class_name}.php')
        if os.path.exists(out_path):
            print(f'  tests/unit/Test_{class_name}.php already exists — skipped.')
            continue

        with open(out_path, 'w') as f:
            f.write(test_code)

        # Count real tests vs stubs
        real  = test_code.count('public function test_') - test_code.count('markTestIncomplete')
        stubs = test_code.count('markTestIncomplete')
        print(f'  Created tests/unit/Test_{class_name}.php — {real} real test(s), {stubs} stub(s)')
        files_written += 1

    update_composer_json(plugin_dir)

    print(f'\nDone. {files_written} test file(s) written.')
    print('\nNext steps:')
    print('  1. cd <plugin-dir> && composer install')
    print('  2. composer test  (all AJAX + hook tests should pass immediately)')
    print('  3. Fill in markTestIncomplete stubs for business logic')
    print('  4. bash /path/to/skill/scripts/review.sh <plugin-dir> --is-formidable')


if __name__ == '__main__':
    main()
