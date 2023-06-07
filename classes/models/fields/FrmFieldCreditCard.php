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

	public function show_on_form_builder( $name = '' ) {
		// TODO Put these styles in frm_admin.css.
		// TODO Possibly put this HTML into a view file.
		echo '<div style="position: relative;">';
		echo '<input type="text" placeholder="1234 1234 1234 1234" disabled />';
		echo '<div style="position: absolute; top: calc( 50% - 1px); transform: translateY(-50%); right: 10px; color: #d0d4dd">';
		FrmAppHelper::icon_by_class( 'frmfont frm_credit_card_icon' );
		echo '</div>'; // Close icon wrapper.
		echo '</div>'; // Close wrapper (that holds both input and icon).
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
