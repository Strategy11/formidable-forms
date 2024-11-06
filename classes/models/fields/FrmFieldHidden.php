<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldHidden extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'hidden';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	protected function field_settings_for_type() {
		$settings            = parent::field_settings_for_type();
		$settings['css']     = false;
		$settings['default'] = true;

		return $settings;
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-hidden.php';
	}

	/**
	 * @return string
	 */
	protected function html5_input_type() {
		return 'hidden';
	}

	/**
	 * @since x.x
	 *
	 * @param array $args
	 * @return array
	 */
	public function validate( $args ) {
		$error = FrmFieldsHelper::get_error_msg( $this->field, 'unique_msg' );
		if ( ! empty( $error ) ) {
			add_filter( 'frm_invalid_error_message', function( $msg ) use ( $error ) {
				return $msg . ' ' . $error;
			} );
		}
		return array();
	}
}
