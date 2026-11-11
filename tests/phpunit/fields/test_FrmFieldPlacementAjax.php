<?php

/**
 * @group ajax
 *
 * @covers FrmFieldsController
 */
class test_FrmFieldPlacementAjax extends FrmAjaxUnitTest {

	public function test_invalid_placement_is_rejected_before_summary_creation_hooks() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		FrmAppHelper::maybe_add_permissions();
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$form_id                           = $this->factory->form->create();
		$values                            = FrmFieldsHelper::setup_new_vars( 'divider', $form_id );
		$values['field_options']['repeat'] = 1;
		$section                           = FrmField::create( $values );
		$before                            = did_action( 'frm_before_create_field' );
		$_POST                             = array(
			'action'        => 'frm_insert_field',
			'nonce'         => wp_create_nonce( 'frm_ajax' ),
			'form_id'       => $form_id,
			'field_type'    => 'summary',
			'section_id'    => $section,
			'field_options' => array( 'in_section' => 0 ),
		);

		$message = '';

		try {
			$this->_handleAjax( 'frm_insert_field' );
		} catch ( WPAjaxDieStopException $error ) {
			$message = $error->getMessage();
		}

		$this->assertSame( $before, did_action( 'frm_before_create_field' ), 'An invalid summary must not run hooks that create page breaks.' );
		$this->assertStringContainsString( 'cannot be placed', $message, 'The builder must receive an explanation of the invalid placement.' );
	}
}
