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
		$form   = FrmForm::getOne( $form_id );
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
			$_REQUEST               = $_POST;
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
			$this->assertSame( $posted_val, $actual_val, 'The default value was not updated correctly for field ' . $field->field_key . '.' );
		}
	}

	/**
	 * @covers FrmFormsController::build_new_form
	 * with ajax
	 */
	public function test_build_new_form_applies_frm_setup_new_form_vars_filter() {
		add_filter( 'frm_setup_new_form_vars', array( $this, '_set_custom_before_html' ) );

		$_POST = array(
			'action' => 'frm_install_form',
			'nonce'  => wp_create_nonce( 'frm_ajax' ),
			'name'   => 'Vivi Setup New Form Vars Test',
			'desc'   => '',
		);
		$_REQUEST = $_POST;

		$response = json_decode( $this->trigger_action( 'frm_install_form' ), true );

		remove_filter( 'frm_setup_new_form_vars', array( $this, '_set_custom_before_html' ) );

		$this->assertNotEmpty( $response['redirect'] ?? '', 'build_new_form did not return a redirect URL.' );
		parse_str( (string) wp_parse_url( $response['redirect'], PHP_URL_QUERY ), $redirect_args );

		$form = FrmForm::getOne( $redirect_args['id'] );
		$this->assertNotEmpty( $form, 'Form not found with id ' . $redirect_args['id'] );
		$this->assertSame( 'VIVI_TEST_MARKER', $form->options['before_html'], 'frm_setup_new_form_vars did not affect the created form.' );
	}

	public function _set_custom_before_html( $values ) {
		$values['before_html'] = 'VIVI_TEST_MARKER';
		return $values;
	}

	/**
	 * @covers FrmFormsController::build_new_form
	 * with ajax
	 */
	public function test_build_new_form_frm_setup_new_form_vars_callback_can_read_existing_key() {
		add_filter( 'frm_setup_new_form_vars', array( $this, '_append_to_before_html' ) );

		$_POST = array(
			'action' => 'frm_install_form',
			'nonce'  => wp_create_nonce( 'frm_ajax' ),
			'name'   => 'Vivi Append Before Html Test',
			'desc'   => '',
		);
		$_REQUEST = $_POST;

		$response = json_decode( $this->trigger_action( 'frm_install_form' ), true );

		remove_filter( 'frm_setup_new_form_vars', array( $this, '_append_to_before_html' ) );

		$this->assertNotEmpty( $response['redirect'] ?? '', 'build_new_form did not return a redirect URL.' );
		parse_str( (string) wp_parse_url( $response['redirect'], PHP_URL_QUERY ), $redirect_args );

		$form = FrmForm::getOne( $redirect_args['id'] );
		$this->assertNotEmpty( $form, 'Form not found with id ' . $redirect_args['id'] );
		$expected = FrmFormsHelper::get_default_html( 'before' ) . '_APPENDED';
		$this->assertSame( $expected, $form->options['before_html'], 'Callback could not read the existing before_html default.' );
	}

	public function _append_to_before_html( $values ) {
		$values['before_html'] .= '_APPENDED';
		return $values;
	}
}
