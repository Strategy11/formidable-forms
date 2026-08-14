<?php

/**
 * @group styles
 */
class test_FrmStyle extends FrmUnitTest {

	/**
	 * @covers FrmStyle::maybe_sanitize_rgba_value
	 */
	public function test_maybe_sanitize_rgba_value() {
		$frm_style            = new FrmStyle();
		$invalid_color_values = array(
			'rgba(45, 45, 45,' => 'rgba(45,45,45,1)',
			'rgba(, , ,1)'     => 'rgba(0,0,0,1)',
			'rgba(45,45'       => 'rgba(45,45,0,1)',
			'rgba(355,,,0.5)'  => 'rgba(255,0,0,0.5)',
			'rgba(255,0,0,11)' => 'rgba(255,0,0,1)',
			'rgba(99,99,99,1)' => 'rgba(99,99,99,1)',
			'rgb(45, ,45)'     => 'rgb(45,0,45)',
			'rgb( , , )'       => 'rgb(0,0,0)',
			'rgb(300,0,-1)'    => 'rgb(255,0,0)',
			'rgb('             => 'rgb(0,0,0)',
			'rgb(255,255,255)' => 'rgb(255,255,255)',
			'(rgba(0,0,0,1)'   => 'rgba(0,0,0,1)',
			'((rgba(0,0,0,1)'  => 'rgba(0,0,0,1)',
			'(rgb(0,0,0)'      => 'rgb(0,0,0)',
			'rgba(0,0,0,1))'   => 'rgba(0,0,0,1)',
			' rgba(0,0,0,1)'   => 'rgba(0,0,0,1)',
			' (rgb(0,0,0)'     => 'rgb(0,0,0)',
			'rgba((0,0,0,1)'   => 'rgba(0,0,0,1)',
		);

		foreach ( $invalid_color_values as $color_val => $expected_color_val ) {
			$this->run_private_method( array( $frm_style, 'maybe_sanitize_rgba_value' ), array( &$color_val ) );
			$this->assertSame( $expected_color_val, $color_val );
		}
	}

	/**
	 * @covers FrmStyle::sanitize_post_content
	 * @covers FrmStyle::strip_invalid_characters
	 */
	public function test_sanitize_post_content() {
		$post_content           = array(
			'bg_color'             => '000',
			'font_size'            => '14px',
			'title_margin_bottom'  => '60px}',
			'field_height'         => ';12px',
			'field_width'          => '{10px',
			'width'                => 'calc(100% / 3)',
			'section_color'        => 'rgba(255,255,255,1)',
			'submit_border_color'  => 'ffffff',
			'submit_active_color'  => 'rgb(255,255,255)',
			'progress_bg_color'    => '000(',
			'success_bg_color'     => ')fff',
			'section_border_width' => '[12px',
			'section_font_size'    => '16px]',
			'unsupported_key'      => 'fff',
			'custom_css'           => '.my-class { color: red; }',
		);
		$frm_style              = new FrmStyle();
		$sanitized_post_content = $frm_style->sanitize_post_content( $post_content );

		$this->assertIsArray( $sanitized_post_content );
		$this->assertSame( '000', $sanitized_post_content['bg_color'] );
		$this->assertSame( '14px', $sanitized_post_content['font_size'] );
		$this->assertSame( '60px', $sanitized_post_content['title_margin_bottom'] );
		$this->assertSame( '12px', $sanitized_post_content['field_height'] );
		$this->assertSame( '10px', $sanitized_post_content['field_width'] );
		$this->assertSame( 'calc(100% / 3)', $sanitized_post_content['width'] );
		$this->assertSame( 'rgba(255,255,255,1)', $sanitized_post_content['section_color'] );
		$this->assertSame( 'ffffff', $sanitized_post_content['submit_border_color'] );
		$this->assertSame( 'rgb(255,255,255)', $sanitized_post_content['submit_active_color'] );
		$this->assertSame( '000', $sanitized_post_content['progress_bg_color'] );
		$this->assertSame( 'fff', $sanitized_post_content['success_bg_color'] );
		$this->assertSame( '12px', $sanitized_post_content['section_border_width'] );
		$this->assertSame( '16px', $sanitized_post_content['section_font_size'] );
		$this->assertSame( '.my-class { color: red; }', $sanitized_post_content['custom_css'] );
		$this->assertArrayNotHasKey( 'unsupported_key', $sanitized_post_content );
	}

	/**
	 * @covers FrmStyle::strip_invalid_characters
	 */
	public function test_strip_invalid_characters() {
		// Make sure that braces don't get added to sizes but removed instead.
		$this->assertSame( '12px', $this->strip_invalid_characters( '12px(' ) );
		$this->assertSame( '2rem', $this->strip_invalid_characters( ')2rem' ) );
		$this->assertSame( '10pt', $this->strip_invalid_characters( '(10pt' ) );
		$this->assertSame( '100%', $this->strip_invalid_characters( '100%)' ) );
		$this->assertSame( '14px', $this->strip_invalid_characters( '(14px)' ) );
		$this->assertSame( '20PX', $this->strip_invalid_characters( ')20PX' ), 'strip_invalid_characters should be case insensitive' );

		// Test CSS vars.
		$this->assertSame( 'var(--grey)', $this->strip_invalid_characters( '(var(--grey)' ) );
		$this->assertSame( 'var(--white)', $this->strip_invalid_characters( '(var(--white)))' ) );

		// Test some calc() rules with extra braces.
		$this->assertSame( 'calc(50%/3)', $this->strip_invalid_characters( '(calc(50%/3)' ) );
		$this->assertSame( 'calc(10%*5)', $this->strip_invalid_characters( ')calc(10%*5)' ) );

		// Test some things that should not change.
		$this->assertSame( 'fff', $this->strip_invalid_characters( 'fff' ) );
		$this->assertSame( '12px', $this->strip_invalid_characters( '12px' ) );
		$this->assertSame( 'rgb(0,0,0)', $this->strip_invalid_characters( 'rgb(0,0,0)' ) );
		$this->assertSame( 'calc(100%/6)', $this->strip_invalid_characters( 'calc(100%/6)' ) );
	}

	/**
	 * @param string $input
	 */
	private function strip_invalid_characters( $input ) {
		$frm_style = new FrmStyle();
		return $this->run_private_method( array( $frm_style, 'strip_invalid_characters' ), array( $input ) );
	}

	/**
	 * @covers FrmStyle::force_balanced_quotation
	 */
	public function test_force_balanced_quotation() {
		$frm_style = new FrmStyle();

		// Test a case where nothing changes.
		$this->assertSame( '"Arial"', $frm_style->force_balanced_quotation( '"Arial"' ) );

		// Balance a missing " at the end.
		$this->assertSame( '"Verdana"', $frm_style->force_balanced_quotation( '"Verdana' ) );

		// Balance a missing ' at the end.
		$this->assertSame( "'Times New Roman'", $frm_style->force_balanced_quotation( "'Times New Roman" ) );

		// Balance a missing " at the front.
		$this->assertSame( '"Helvetica"', $frm_style->force_balanced_quotation( 'Helvetica"' ) );

		// Balance a missing ' at the front.
		$this->assertSame( "'Comic Sans'", $frm_style->force_balanced_quotation( "Comic Sans'" ) );
	}

	/**
	 * @gcovers FrmStyle::trim_braces
	 */
	public function test_trim_braces() {
		$this->assertSame( 'calc(100%)', $this->trim_braces( '(calc(100%)))' ) );
		$this->assertSame( 'skewX(5px)', $this->trim_braces( '((skewX(5px)' ) );
		$this->assertSame( 'var(--grey)', $this->trim_braces( '(var(--grey))' ) );
		$this->assertSame( 'scale(2)', $this->trim_braces( '(scale(2)))' ) );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function trim_braces( $value ) {
		$frm_style = new FrmStyle();
		return $this->run_private_method( array( $frm_style, 'trim_braces' ), array( $value ) );
	}

	/**
	 * Regression test for the bug this fix addresses: the legacy `gmdate( 'njGi' )` cache-busting
	 * version omitted the year and concatenated unpadded month/day/hour, so distinct dates could
	 * produce an identical version string. This is documented directly (no clock mocking needed,
	 * since gmdate() accepts an explicit timestamp), then the new content-derived version is shown
	 * to both distinguish and, when content really is unchanged, correctly match across those same
	 * colliding moments -- proving the new value depends on content, not the clock.
	 *
	 * @covers FrmStyle::update_css_version
	 */
	public function test_update_css_version_does_not_collide_across_previously_colliding_dates() {
		// 2026-01-01 10:59 UTC, 2026-01-11 00:59 UTC and 2026-11-01 00:59 UTC.
		$colliding_timestamps = array(
			gmmktime( 10, 59, 0, 1, 1, 2026 ),
			gmmktime( 0, 59, 0, 1, 11, 2026 ),
			gmmktime( 0, 59, 0, 11, 1, 2026 ),
		);

		$legacy_versions = array();

		foreach ( $colliding_timestamps as $timestamp ) {
			$legacy_versions[] = gmdate( 'njGi', $timestamp );
		}

		// Document the bug being fixed: the legacy format collides across all three dates.
		$this->assertSame(
			array( '111059', '111059', '111059' ),
			$legacy_versions,
			'Sanity check that these three dates are the ones known to collide under the legacy gmdate( "njGi" ) format.'
		);

		delete_option( 'frm_last_style_update' );

		// Distinct content "saved" at each of those colliding moments must produce distinct
		// versions. This is the property the legacy format could not provide.
		$distinct_versions = array();

		foreach ( $colliding_timestamps as $index => $timestamp ) {
			$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( 'body{--collision-check:' . $index . '}' ) );
			$distinct_versions[] = get_option( 'frm_last_style_update' );
		}

		$this->assertCount( 3, array_unique( $distinct_versions ), 'The content-derived version must not collide across the three previously-colliding dates.' );

		// Identical content "saved" at each of those same moments must produce the SAME version,
		// proving the derivation is clock-independent rather than merely higher resolution.
		$stable_versions = array();

		foreach ( $colliding_timestamps as $timestamp ) {
			$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( 'body{color:#123456}' ) );
			$stable_versions[] = get_option( 'frm_last_style_update' );
		}

		$this->assertCount( 1, array_unique( $stable_versions ), 'Identical content must resolve to the same version regardless of when it is saved.' );
	}

	/**
	 * @covers FrmStyle::update_css_version
	 */
	public function test_update_css_version_is_sensitive_to_content_in_both_directions() {
		delete_option( 'frm_last_style_update' );

		$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( 'body{color:#111111}' ) );
		$version_a = get_option( 'frm_last_style_update' );
		$this->assertNotEmpty( $version_a );

		// Different CSS must produce a different version.
		$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( 'body{color:#222222}' ) );
		$version_b = get_option( 'frm_last_style_update' );
		$this->assertNotSame( $version_a, $version_b, 'Different CSS content must produce a different version.' );

		// Identical CSS must produce the identical version (this is why a hash was chosen over
		// time() -- an unchanged save should not needlessly invalidate downstream caches).
		$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( 'body{color:#111111}' ) );
		$version_a_again = get_option( 'frm_last_style_update' );
		$this->assertSame( $version_a, $version_a_again, 'Identical CSS content must produce the identical version.' );
	}

	/**
	 * Two consecutive saves in the same PHP process with different content must produce different
	 * versions, and each version must be derived from that content alone. This fails by
	 * construction against the legacy gmdate( 'njGi' ) implementation, which is minute-granularity
	 * and would store the identical value for both saves.
	 *
	 * The clock-independence claim is asserted against the content, not against the wall clock. An
	 * earlier form of this test first sanity-checked that both saves landed in the same legacy
	 * gmdate( 'njGi' ) bucket, which fails whenever two consecutive calls happen to straddle a
	 * minute boundary -- a red build for correct product behaviour, on a premise the assertions
	 * below do not actually need. Pinning each version to the hash of its own content proves the
	 * property outright rather than relying on the run being lucky.
	 *
	 * @covers FrmStyle::update_css_version
	 */
	public function test_consecutive_saves_with_different_content_produce_content_derived_versions() {
		delete_option( 'frm_last_style_update' );

		$css_1 = 'body{color:#aaaaaa}';
		$css_2 = 'body{color:#bbbbbb}';

		$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( $css_1 ) );
		$version_1 = get_option( 'frm_last_style_update' );

		$this->run_private_method( array( 'FrmStyle', 'update_css_version' ), array( $css_2 ) );
		$version_2 = get_option( 'frm_last_style_update' );

		$this->assertNotEmpty( $version_1 );
		$this->assertNotEmpty( $version_2 );
		$this->assertNotSame( $version_1, $version_2, 'Two consecutive saves with different content must produce different versions.' );

		// Each version is the hash of its own content and nothing else. No minute-granularity
		// value -- or any other clock-derived one -- can satisfy both of these assertions, which
		// is the reversion this test exists to catch.
		$this->assertSame( substr( md5( $css_1 ), 0, 12 ), $version_1, 'The version must be derived from the stylesheet content alone.' );
		$this->assertSame( substr( md5( $css_2 ), 0, 12 ), $version_2, 'The version must be derived from the stylesheet content alone.' );
	}

	/**
	 * Anti-reversion guard: the legacy date-based version must not creep back into save_settings().
	 *
	 * @covers FrmStyle::save_settings
	 */
	public function test_save_settings_does_not_reintroduce_legacy_date_based_version() {
		$method = new ReflectionMethod( 'FrmStyle', 'save_settings' );
		$lines  = file( $method->getFileName() );
		$body   = implode( '', array_slice( $lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1 ) );

		$this->assertStringNotContainsString( 'njGi', $body, 'save_settings() must not reintroduce the legacy gmdate( "njGi" ) cache-busting version.' );
	}

	/**
	 * Regression guard: because the version is a content hash, publishing it for a write that
	 * silently failed is unrecoverable (every later save of the same content reproduces the same
	 * hash and URL, permanently pinning a downstream cache to the stale file). This forces a real
	 * write failure -- via a genuine filesystem directory collision, not a mock -- and asserts
	 * frm_last_style_update is left completely untouched: neither overwritten nor deleted.
	 *
	 * The sentinel assertion on its own would also hold if save_settings() never got as far as
	 * FrmCreateFile::create_file() -- it returns early when css/custom_theme.css.php is missing,
	 * which would make this test pass without exercising the failed write at all. Two guards
	 * close that off: the source stylesheet is asserted to exist before the call, and frmpro_css
	 * is asserted to have been populated afterwards. The option is only written after
	 * create_file() has returned, so a populated frmpro_css alongside an untouched version proves
	 * the write was attempted, reported failure, and the version write was skipped for that
	 * reason.
	 *
	 * @covers FrmStyle::save_settings
	 */
	public function test_save_settings_leaves_version_untouched_when_file_write_fails() {
		add_filter( 'frm_add_css_to_uploads_dir', '__return_true' );

		$uploads      = wp_upload_dir();
		$blocked_path = $uploads['basedir'] . '/formidable';

		// A previous save (in this test or another) may have already created this as a real
		// directory. It only holds generated/cache data, so it is safe to clear it to guarantee a
		// deterministic collision below.
		$this->rmdir_recursive( $blocked_path );

		try {
			$this->assertNotFalse( file_put_contents( $blocked_path, 'not a directory' ), 'Test setup: failed to create the blocking file.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			$this->assertTrue( is_file( $blocked_path ), 'Test setup assumption: the blocking path must be a plain file, not a directory, so FrmCreateFile cannot create its "formidable" subdirectory there.' );
			$this->assertTrue(
				is_file( FrmAppHelper::plugin_path() . '/css/custom_theme.css.php' ),
				'Test setup assumption: the source stylesheet must exist, or save_settings() returns before FrmCreateFile::create_file() and this test asserts nothing.'
			);

			update_option( 'frm_last_style_update', 'sentinel-untouched' );
			delete_option( 'frmpro_css' );

			$frm_style = new FrmStyle( 'default' );
			$frm_style->save_settings();

			$this->assertNotEmpty(
				get_option( 'frmpro_css' ),
				'The generated CSS must have been stored, which only happens once FrmCreateFile::create_file() has been called -- otherwise the failed write was never exercised.'
			);
			$this->assertSame( 'sentinel-untouched', get_option( 'frm_last_style_update' ), 'frm_last_style_update must be left untouched (not updated, not deleted) when the CSS file write fails.' );
		} finally {
			remove_filter( 'frm_add_css_to_uploads_dir', '__return_true' );

			if ( is_file( $blocked_path ) ) {
				unlink( $blocked_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}
	}

	/**
	 * @param string $path
	 *
	 * @return void
	 */
	private function rmdir_recursive( $path ) {
		if ( is_file( $path ) ) {
			unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			return;
		}

		if ( ! is_dir( $path ) ) {
			return;
		}

		foreach ( array_diff( scandir( $path ), array( '.', '..' ) ) as $item ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_scandir
			$this->rmdir_recursive( $path . '/' . $item );
		}

		rmdir( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}

	/**
	 * The version must never advertise content the AJAX fallback ( frmpro_css option/transient )
	 * is not already serving. Whenever save_settings() advances the version, frmpro_css must
	 * already be populated with exactly the content that hash was derived from.
	 *
	 * This is an ordering claim, so it is asserted at the ordering boundary rather than after the
	 * fact: a pre_update_option_frm_last_style_update filter records what frmpro_css held at the
	 * instant the version write was attempted. Checking only the end state would let a regression
	 * that advances the version first and stores the CSS afterwards pass, since the final values
	 * agree either way. The end-state assertions are kept as well, so both the ordering and the
	 * resulting consistency are covered.
	 *
	 * The invariant is asserted for every observed write rather than for a single expected one.
	 * save_settings() can legitimately run more than once per call: get_css_content() renders
	 * custom_theme.css.php, which reads FrmStyle::get_all(), which creates a default style and
	 * calls update( 'default' ) -- and therefore save_settings() again -- when no style rows exist
	 * yet. How many writes happen is incidental and depends on the state the suite leaves behind;
	 * that each one is ordered after its own frmpro_css write is the property that matters.
	 *
	 * @covers FrmStyle::save_settings
	 */
	public function test_frmpro_css_is_populated_before_version_advances() {
		delete_option( 'frm_last_style_update' );
		delete_option( 'frmpro_css' );
		delete_transient( 'frmpro_css' );

		$observed = array();
		$observer = function ( $value ) use ( &$observed ) {
			$observed[] = array(
				'version'   => $value,
				'option'    => get_option( 'frmpro_css' ),
				'transient' => get_transient( 'frmpro_css' ),
			);

			return $value;
		};

		add_filter( 'pre_update_option_frm_last_style_update', $observer );

		try {
			$frm_style = new FrmStyle( 'default' );
			$frm_style->save_settings();
		} finally {
			remove_filter( 'pre_update_option_frm_last_style_update', $observer );
		}

		$version = get_option( 'frm_last_style_update' );
		$this->assertNotEmpty( $version, 'Test setup assumption: the write should succeed and the version should advance in this environment.' );

		$this->assertNotEmpty( $observed, 'The version write must have been observed, or this test asserts nothing about the ordering.' );

		foreach ( $observed as $index => $at_write ) {
			$where = ' (version write ' . ( $index + 1 ) . ' of ' . count( $observed ) . ')';

			$this->assertNotEmpty( $at_write['option'], 'The frmpro_css option must already be populated at the moment the version is written, not afterwards.' . $where );
			$this->assertNotEmpty( $at_write['transient'], 'The frmpro_css transient must already be populated at the moment the version is written, not afterwards.' . $where );
			$this->assertSame(
				$at_write['version'],
				substr( md5( $at_write['option'] ), 0, 12 ),
				'The version being written must hash the CSS already in frmpro_css, so the URL never advertises content the fallback is not serving.' . $where
			);
			$this->assertSame(
				$at_write['option'],
				$at_write['transient'],
				'The frmpro_css option and transient must already agree at the moment the version is written.' . $where
			);
		}

		$stored_css    = get_option( 'frmpro_css' );
		$transient_css = get_transient( 'frmpro_css' );

		$this->assertNotEmpty( $stored_css, 'The frmpro_css option must be populated whenever the version advances.' );
		$this->assertNotEmpty( $transient_css, 'The frmpro_css transient must be populated whenever the version advances.' );
		$this->assertSame( $stored_css, $transient_css, 'The frmpro_css option and transient must agree.' );
		$this->assertSame(
			$version,
			substr( md5( $stored_css ), 0, 12 ),
			'The advanced version must correspond exactly to the frmpro_css content already stored, so the enqueued URL and the AJAX fallback can never disagree.'
		);
	}
}
