<?php

class test_FrmEmailStylesController extends FrmUnitTest {

	public function test_add_inline_css() {
		$content     = '<div><p style="font-weight:bold;"><a href="#" target="_blank">Test link</a></p></div><div>Second div</div>';
		$css         = 'font-size:14px;';
		$new_content = '<div style="' . $css . '"><p style="font-weight:bold;"><a href="#" target="_blank">Test link</a></p></div><div style="' . $css . '">Second div</div>';
		$this->assertEquals(
			$this->run_private_method(
				array( 'FrmEmailStylesController', 'add_inline_css' ),
				array( 'div', $css, $content )
			),
			$new_content
		);

		$new_content = '<div><p style="' . $css . 'font-weight:bold;"><a href="#" target="_blank">Test link</a></p></div><div>Second div</div>';
		$this->assertEquals(
			$this->run_private_method(
				array( 'FrmEmailStylesController', 'add_inline_css' ),
				array( 'p', $css, $content )
			),
			$new_content
		);

		$new_content = '<div><p style="font-weight:bold;"><a style="' . $css . '" href="#" target="_blank">Test link</a></p></div><div>Second div</div>';
		$this->assertEquals(
			$this->run_private_method(
				array( 'FrmEmailStylesController', 'add_inline_css' ),
				array( 'a', $css, $content )
			),
			$new_content
		);
	}
}
