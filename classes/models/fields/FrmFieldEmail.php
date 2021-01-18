<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldEmail extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'email';
	protected $display_type = 'text';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
		);
	}

	/**
	 * Validate the email format
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function validate( $args ) {
		$errors = array();
		if ( $args['value'] != '' && ! is_email( $args['value'] ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		return $errors;
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_email', $value );
	}
}
