<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmFieldPhone extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'phone';

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
			'invalid'        => true,
		);
	}

	/**
	 * @since 6.9
	 *
	 * @param array $args Includes 'field', 'display', and 'values'.
	 *
	 * @return void
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];

		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/phone/phone-type.php';
		FrmFieldsController::show_format_option( $field );

		parent::show_primary_options( $args );
	}

	/**
	 * Retrieves the HTML for an 'International' option in a dropdown.
	 *
	 * @since 6.9
	 *
	 * @return void Outputs the HTML option tag directly.
	 */
	protected function print_international_option() {
		?>
		<option
			value="international"
			class="frm_show_upgrade frm_noallow"
			data-upgrade="<?php esc_attr_e( 'International phone field', 'formidable' ); ?>"
			data-medium="international-phone-field"
			<?php selected( FrmField::get_option( $this->field, 'format' ), 'international' ); ?>
		>
			<?php esc_html_e( 'International', 'formidable' ); ?>
		</option>
		<?php
	}

	/**
	 * @return string
	 */
	protected function html5_input_type() {
		$frm_settings = FrmAppHelper::get_settings();

		return $frm_settings->use_html ? 'tel' : 'text';
	}

	/**
	 * @since 4.0.04
	 *
	 * @return void
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
