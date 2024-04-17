<?php

/**
 * @group ajax
 */
class test_FrmFormsControllerAjax extends FrmAjaxUnitTest {

	public function setUp(): void {
		parent::setUp();

		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );
	}

	/**
	 * @covers FrmFormsController::update
	 * with ajax
	 */
	public function test_form_update_with_ajax() {
		$form_id = $this->factory->form->get_id_by_key( $this->contact_form_key );
		$this->assertNotEmpty( $form_id, 'Form not found with key ' . $this->contact_form_key );

		self::_setup_post_values( $form_id );

		try {
			$this->_handleAjax( 'frm_save_form' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
			// Expected to return form successfully updated message
		}

		self::_check_updated_values( $form_id );
	}

	private function _setup_post_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		$form = FrmForm::getOne( $form_id );
		$this->assertNotEmpty( $form, 'Form not found with id ' . $form_id );

		$_POST = array(
			'page'                 => 'formidable',
			'frm_action'           => 'update',
			'id'                   => $form_id,
			'action'               => 'update',
			'frm_save_form'        => wp_create_nonce( 'frm_save_form_nonce' ),
			'status'               => 'published',
			'new_status'           => '',
			'name'                 => $form->name,
			'frm_fields_submitted' => array(),
			'item_meta'            => array(),
			'field_options'        => array(),
		);

		foreach ( $fields as $field ) {
			$_POST['frm_fields_submitted'][]        = $field->id;
			$_POST[ 'default_value_' . $field->id ] = 'default';

			$field_options = array(
				'description_' . $field->id        => '',
				'type_' . $field->id               => '',
				'required_indicator_' . $field->id => '*',
				'field_key_' . $field->id          => $field->field_key,
				'classes_' . $field->id            => '',
				'label_' . $field->id              => '',
				'size_' . $field->id               => '',
				'max_' . $field->id                => '',
				'admin_only_' . $field->id         => '',
				'use_calc_' . $field->id           => 1,
				'calc_' . $field->id               => '',
				'calc_dec_' . $field->id           => '',
				'show_hide_' . $field->id          => 'show',
				'any_all_' . $field->id            => 'any',
				'blank_' . $field->id              => 'This field cannot be blank.',
				'unique_msg_' . $field->id         => '',
			);

			$_POST['field_options'] = array_merge( $_POST['field_options'], $field_options );

			$_REQUEST = $_POST;
		}
	}

	private function _check_updated_values( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		// Compare to posted values
		foreach ( $fields as $field ) {
			if ( FrmField::is_no_save_field( $field->type ) ) {
				continue;
			}

			// Check default value
			$posted_val = $_POST[ 'default_value_' . $field->id ];
			$actual_val = $field->default_value;
			$this->assertEquals( $posted_val, $actual_val, 'The default value was not updated correctly for field ' . $field->field_key . '.' );
		}
	}
}
