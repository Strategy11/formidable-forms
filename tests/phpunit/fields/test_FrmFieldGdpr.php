<?php
/**
 * @group fields
 */
class test_FrmFieldGdpr extends FrmUnitTest {

	/**
	 * @covers FrmFieldGdpr
	 */
	public function test_label_does_not_duplicate_for_attribute_when_wrapping_input() {
		$frm_settings              = FrmAppHelper::get_settings();
		$original_enable_gdpr      = $frm_settings->enable_gdpr;
		$frm_settings->enable_gdpr = true;

		try {
			$form_id = $this->factory->form->create();
			$field   = $this->factory->field->create_and_get(
				array(
					'type'    => 'gdpr',
					'form_id' => $form_id,
				)
			);

			$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
			$field_object = FrmFieldFactory::get_field_type( 'gdpr', $field_array );

			$html = $field_object->prepare_field_html(
				array(
					'errors' => array(),
					'form'   => FrmForm::getOne( $form_id ),
				)
			);
		} finally {
			$frm_settings->enable_gdpr = $original_enable_gdpr;
		}

		$this->assert_label_wraps_input_without_for( $html, 'GDPR' );
	}

	/**
	 * @covers FrmFieldGdpr
	 */
	public function test_disabled_notice_label_has_no_dangling_for_attribute() {
		$frm_settings              = FrmAppHelper::get_settings();
		$original_enable_gdpr      = $frm_settings->enable_gdpr;
		$frm_settings->enable_gdpr = false;
		$user_id                   = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		FrmAppHelper::maybe_add_permissions();
		// maybe_add_permissions() adds the frm_* caps to the administrator role, but the
		// already-instantiated current user's cached allcaps doesn't pick that up on its own.
		wp_get_current_user()->get_role_caps();

		try {
			$form_id = $this->factory->form->create();
			$field   = $this->factory->field->create_and_get(
				array(
					'type'    => 'gdpr',
					'form_id' => $form_id,
				)
			);

			$field_array  = FrmFieldsHelper::setup_edit_vars( $field );
			$field_object = FrmFieldFactory::get_field_type( 'gdpr', $field_array );

			$html = $field_object->prepare_field_html(
				array(
					'errors' => array(),
					'form'   => FrmForm::getOne( $form_id ),
				)
			);
		} finally {
			$frm_settings->enable_gdpr = $original_enable_gdpr;
			wp_set_current_user( 0 );
		}

		$this->assertStringContainsString( 'GDPR field is disabled', $html, 'Expected the disabled-notice branch to render' );
		$this->assertDoesNotMatchRegularExpression( '/<label[^>]*\bfor=/', $html, 'The disabled-notice label has no input to associate with, so it should not carry a for attribute' );
	}
}
