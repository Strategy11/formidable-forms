<?php
/**
 * @group fields
 */
class test_FrmFieldHTML extends FrmUnitTest {
	/**
	 * @covers FrmFieldHTML::show_primary_options
	 */
	public function test_wp_editor_skips_init_on_render() {
		// Force rich editing on so wp_editor() actually registers a TinyMCE
		// entry for this field instead of quicktags-only.
		add_filter( 'user_can_richedit', '__return_true' );

		$field_object = new FrmFieldHTML();
		$html_id      = 'frm_description_98765';

		ob_start();
		$field_object->show_primary_options(
			array(
				'field' => array(
					'id'          => 98765,
					'description' => 'some content',
					'type'        => 'html',
				),
			)
		);
		// The actual tinyMCEPreInit.mceInit assignment is only printed by
		// _WP_Editors::editor_js(), normally called from the admin footer.
		_WP_Editors::editor_js();
		$output         = ob_get_clean();
		$mce_init_start = strpos( $output, 'mceInit: {' );
		$qt_init_start  = strpos( $output, 'qtInit: {' );
		$this->assertNotFalse( $mce_init_start, 'Expected _WP_Editors::editor_js() to print a mceInit block' );
		$this->assertNotFalse( $qt_init_start, 'Expected _WP_Editors::editor_js() to print a qtInit block' );

		$mce_init_block = substr( $output, $mce_init_start, $qt_init_start - $mce_init_start );

		$this->assertStringContainsString( "'{$html_id}':{", $mce_init_block );
		$this->assertStringContainsString(
			'wp_skip_init:true',
			$mce_init_block,
			'HTML field editor should be flagged wp_skip_init so it does not auto-boot on page load'
		);
	}
}
