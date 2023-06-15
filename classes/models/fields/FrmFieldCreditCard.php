<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldCreditCard extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'credit_card';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	protected function field_settings_for_type() {
		$settings = array(
			'clear_on_focus' => false,
			'description'    => false,
			'default'        => false,
			'required'       => false,
		);
		return $settings;
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-credit-card.php';
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$pass_args = array(
			'errors'   => $args['errors'],
			'html_id'  => $args['html_id'],
			'field_id' => $args['field_id'],
		);

		ob_start();
		FrmStrpLiteActionsController::show_card( $this->field, $args['field_name'], $pass_args );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}

}
