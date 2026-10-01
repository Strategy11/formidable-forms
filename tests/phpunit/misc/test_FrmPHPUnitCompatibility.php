<?php

/**
 * Verify the compatibility layer keeps WordPress deprecation checks active.
 *
 * @group phpunit-compatibility
 */
#[\PHPUnit\Framework\Attributes\Group( 'phpunit-compatibility' )]
class test_FrmPHPUnitCompatibility extends FrmUnitTest {

	/**
	 * @expectedDeprecated frm_test_deprecated
	 */
	public function test_expected_deprecation_annotation() {
		_deprecated_function( 'frm_test_deprecated', '1.0' );
		$this->assertArrayHasKey( 'frm_test_deprecated', $this->caught_deprecated );
	}

	/**
	 * @expectedIncorrectUsage frm_test_incorrect_usage
	 */
	public function test_expected_incorrect_usage_annotation() {
		_doing_it_wrong( 'frm_test_incorrect_usage', 'Test incorrect usage.', '1.0' );
		$this->assertArrayHasKey( 'frm_test_incorrect_usage', $this->caught_doing_it_wrong );
	}

	public function test_programmatic_deprecation_expectation() {
		$this->setExpectedDeprecated( 'frm_test_programmatic_deprecated' );
		_deprecated_function( 'frm_test_programmatic_deprecated', '1.0' );
		$this->expectedDeprecated();
	}

	public function test_unexpected_deprecation_fails() {
		_deprecated_function( 'frm_test_unexpected_deprecated', '1.0' );
		$failed = false;

		try {
			$this->expectedDeprecated();
		} catch ( \PHPUnit\Framework\ExpectationFailedException $exception ) {
			$failed = true;
			$this->assertStringContainsString( 'Unexpected deprecation notice', $exception->getMessage() );
		}

		$this->setExpectedDeprecated( 'frm_test_unexpected_deprecated' );
		$this->assertTrue( $failed, 'Unexpected deprecations must still fail the test.' );
	}

	public function test_missing_expected_deprecation_fails() {
		$this->setExpectedDeprecated( 'frm_test_missing_deprecated' );
		$failed = false;

		try {
			$this->expectedDeprecated();
		} catch ( \PHPUnit\Framework\ExpectationFailedException $exception ) {
			$failed = true;
			$this->assertStringContainsString( 'triggered a deprecation notice', $exception->getMessage() );
		}

		_deprecated_function( 'frm_test_missing_deprecated', '1.0' );
		$this->assertTrue( $failed, 'Missing expected deprecations must still fail the test.' );
	}
}
