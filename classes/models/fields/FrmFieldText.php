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
	 * Print the format number option for a field.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public function print_format_number_option() {
		?>
		<option
			value=""
			class="frm_show_upgrade frm_noallow"
			data-upgrade="<?php esc_attr_e( 'Format number field', 'formidable' ); ?>"
			data-medium="format-number-field"
		>
			<?php esc_html_e( 'Number', 'formidable' ); ?>
		</option>
		<?php
	}
}
