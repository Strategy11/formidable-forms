<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldDefault extends FrmFieldType {

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @param $type string
	 */
	protected function set_type( $type ) {
		if ( empty( $type ) ) {
			$type = 'text';
		}
		parent::set_type( $type );
	}

	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );

		ob_start();
		do_action( 'frm_display_added_fields', $field );
		do_action( 'frm_display_added_' . $this->type . '_field', $field );
		$input_html = ob_get_contents();
		ob_end_clean();

		if ( empty( $input_html ) ) {
			echo $this->builder_text_field( $name ); // WPCS: XSS ok.
		} else {
			echo $input_html; // WPCS: XSS ok.
		}
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$pass_args = array(
			'errors'  => $args['errors'],
			'html_id' => $args['html_id'],
		);
		ob_start();
		do_action( 'frm_form_fields', $this->field, $args['field_name'], $pass_args );
		do_action( 'frm_form_field_' . $this->type, $this->field, $args['field_name'], $pass_args );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}
}
