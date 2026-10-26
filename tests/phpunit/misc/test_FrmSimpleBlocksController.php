<?php

/**
 * @covers FrmSimpleBlocksController
 */
#[\PHPUnit\Framework\Attributes\CoversClass( FrmSimpleBlocksController::class )]
class test_FrmSimpleBlocksController extends FrmUnitTest {

	/**
	 * Block options should share the lightweight published form list.
	 *
	 * @return void
	 */
	public function test_form_options_use_published_form_names() {
		$form     = $this->factory->form->create(
			array(
				'name'   => 'Block form',
				'status' => 'published',
			)
		);
		$draft    = $this->factory->form->create( array( 'status' => 'draft' ) );
		$child    = $this->factory->form->create(
			array(
				'status'         => 'published',
				'parent_form_id' => $form,
			)
		);
		$template = $this->factory->form->create(
			array(
				'status'      => 'published',
				'is_template' => 1,
			)
		);
		$options  = $this->run_private_method( array( 'FrmSimpleBlocksController', 'get_forms_options' ) );
		$ids      = array_map( 'intval', array_column( $options, 'value' ) );
		$this->assertContains( $form, $ids, 'Published forms should be available to blocks.' );
		$this->assertNotContains( $draft, $ids, 'Draft forms must be excluded.' );
		$this->assertNotContains( $child, $ids, 'Child forms must be excluded.' );
		$this->assertNotContains( $template, $ids, 'Templates must be excluded.' );
		$option = $options[ array_search( $form, $ids, true ) ];
		$this->assertSame( 'Block form', $option['label'], 'Form labels should be preserved.' );
	}

	public function test_maybe_remove_fade_on_load_for_block_preview() {
		$form = '<form enctype="multipart/form-data" method="post" class="frm-show-form  frm_pro_form  frm_logic_form  frm-admin-viewing ">';

		$_SERVER['HTTP_ACCEPT']  = 'application/json';
		$_SERVER['CONTENT_TYPE'] = 'application/json';

		if ( is_callable( 'wp_is_json_request' ) ) {
			$this->assertStringNotContainsString( 'frm_logic_form', $this->maybe_remove_fade_on_load_for_block_preview( $form ) );
		}

		unset( $_SERVER['HTTP_ACCEPT'], $_SERVER['CONTENT_TYPE'] );
		$this->assertStringContainsString( 'frm_logic_form', $this->maybe_remove_fade_on_load_for_block_preview( $form ) );
	}

	private function maybe_remove_fade_on_load_for_block_preview( $form ) {
		return $this->run_private_method( array( 'FrmSimpleBlocksController', 'maybe_remove_fade_on_load_for_block_preview' ), array( $form ) );
	}
}
