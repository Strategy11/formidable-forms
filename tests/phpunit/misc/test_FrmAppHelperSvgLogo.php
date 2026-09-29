<?php

/**
 * @group app
 */
class test_FrmAppHelperSvgLogo extends FrmUnitTest {

	/**
	 * @covers FrmAppHelper::svg_logo
	 */
	public function test_svg_logo_is_decorative() {
		$icon = FrmAppHelper::svg_logo();
		$this->assertStringContainsString( 'aria-hidden="true"', $icon );
	}

	/**
	 * @covers FrmAppHelper::show_header_logo
	 */
	public function test_show_header_logo_is_decorative() {
		ob_start();
		FrmAppHelper::show_header_logo();
		$output = ob_get_clean();

		$this->assertSame( 1, substr_count( $output, 'aria-hidden' ) );
	}
}
