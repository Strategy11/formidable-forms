<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHoneypot extends FrmValidate {

	/**
	 * @return string
	 */
	protected function get_option_key() {
		return 'honeypot';
	}

	/**
	 * @return bool
	 */
	public function validate() {
		if ( ! $this->is_option_on() || ! $this->check_honeypot_filter() ) {
			// never flag as honeypot spam if disabled.
			return true;
		}
		return ! $this->is_honeypot_spam();
	}

	/**
	 * @return bool
	 */
	private function is_honeypot_spam() {
		$is_honeypot_spam = $this->is_legacy_honeypot_spam();
		if ( ! $is_honeypot_spam ) {
			// Check the newer honeypot input name which is randomly generated so it's more difficult to detect.
			$class_name       = self::get_honeypot_class_name();
			$honeypot_value   = FrmAppHelper::get_param( $class_name, '', 'get', 'sanitize_text_field' );
			$is_honeypot_spam = '' !== $honeypot_value;
		}

		$form = $this->get_form();
		$atts = compact( 'form' );
		return apply_filters( 'frm_process_honeypot', $is_honeypot_spam, $atts );
	}

	/**
	 * Check the old frm_verify key. We'll continue to consider any entry with an frm_verify value as spam.
	 *
	 * @return bool
	 */
	private function is_legacy_honeypot_spam() {
		$legacy_honeypot_value = FrmAppHelper::get_param( 'frm_verify', '', 'get', 'sanitize_text_field' );
		return '' !== $legacy_honeypot_value;
	}

	/**
	 * @return mixed either true, or false.
	 */
	private function check_honeypot_filter() {
		$form = $this->get_form();
		return apply_filters( 'frm_run_honeypot', true, compact( 'form' ) );
	}

	/**
	 * @return string
	 */
	private function check_honeypot_setting() {
		$form = $this->get_form();
		$key  = $this->get_option_key();
		return $form->options[ $key ];
	}

	/**
	 * @param int $form_id
	 *
	 * @return void
	 */
	public static function maybe_render_field( $form_id ) {
		$honeypot = new self( $form_id );
		if ( $honeypot->should_render_field() ) {
			$honeypot->render_field();
		}
	}

	/**
	 * @return bool
	 */
	public function should_render_field() {
		return $this->is_option_on() && $this->check_honeypot_filter();
	}

	/**
	 * @return void
	 */
	public function render_field() {
		$honeypot    = $this->check_honeypot_setting();
		$form        = $this->get_form();
		$class_name  = self::get_honeypot_class_name();
		$input_attrs = array(
			'id'    => 'frm_email_' . absint( $form->id ),
			'type'  => 'strict' === $honeypot ? 'email' : 'text',
			'class' => 'frm_verify',
			'name'  => $class_name,
			'value' => FrmAppHelper::get_param( $class_name, '', 'get', 'wp_kses_post' ),
		);

		if ( 'strict' !== $honeypot ) {
			$input_attrs['autocomplete'] = 'false';
		}
		?>
			<div class="<?php echo esc_attr( $class_name ); ?>">
				<label for="frm_email_<?php echo esc_attr( $form->id ); ?>" <?php FrmFormsHelper::maybe_hide_inline(); ?>>
					<?php esc_html_e( 'If you are human, leave this field blank.', 'formidable' ); ?>
				</label>
				<input <?php FrmAppHelper::array_to_html_params( $input_attrs, true ); ?> <?php FrmFormsHelper::maybe_hide_inline(); ?> />
			</div>
		<?php
	}

	/**
	 * Generate a random class name for our honeypot so it is less easy to detect.
	 *
	 * @return string The generated class name.
	 */
	public static function generate_class_name() {
		$class_name = self::get_honeypot_class_name();
		if ( 'frm_verify' !== $class_name ) {
			// Re-use the option.
			// We can't generate a new class too often or the field may not be hidden.
			return $class_name;
		}

		$prefix     = 'frm__';
		$class_name = $prefix . uniqid();
		update_option( 'frm_honeypot_class', $class_name );
		return $class_name;
	}

	/**
	 * @return string The current class name to use the for Honeypot field.
	 */
	private static function get_honeypot_class_name() {
		$option = get_option( 'frm_honeypot_class' );
		if ( ! is_string( $option ) ) {
			// For backward compatibility use the old class name.
			return 'frm_verify';
		}
		return $option;
	}
}
