<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldText extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'text';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @var bool
	 */
	protected $array_allowed = false;

	/**
	 * @return bool[]
	 */
	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'format'         => true,
			'invalid'        => true,
		);
	}

	public function validate( $args ) {
		$errors     = parent::validate( $args );
		$max_length = intval( FrmField::get_option( $this->field, 'max' ) );

		if ( $max_length && FrmAppHelper::mb_function( array( 'mb_strlen', 'strlen' ), array( $args['value'] ) ) > $max_length ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		return $errors;
	}

	/**
	 * If the value includes intentional entities, don't lose them.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	protected function prepare_esc_value() {
		$value = $this->field['value'];
		if ( is_null( $value ) ) {
			return '';
		}
		if ( strpos( $value, '&lt;' ) !== false ) {
			$value = str_replace( '&amp;', '&', htmlentities( $value ) );
		}
		return $value;
	}
}
