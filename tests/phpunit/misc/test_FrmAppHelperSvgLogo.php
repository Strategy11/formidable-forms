<?php

/**
 * @group app
 *
 * @covers FrmAppHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'app' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmAppHelper::class )]
class test_FrmAppHelperSvgLogo extends FrmUnitTest {

	public function test_svg_logo_is_decorative() {
		$icon = FrmAppHelper::svg_logo();
		$this->assertStringContainsString( 'aria-hidden="true"', $icon );
	}

	public function test_show_header_logo_is_decorative() {
		ob_start();
		FrmAppHelper::show_header_logo();
		$output = ob_get_clean();

		$this->assertSame( 1, substr_count( $output, 'aria-hidden' ) );
	}
}
