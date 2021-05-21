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
	 * @return boolean
	 */
	private function is_honeypot_spam() {
		$honeypot_value   = FrmAppHelper::get_param( 'frm_verify', '', 'get', 'sanitize_text_field' );
		$is_honeypot_spam = $honeypot_value !== '';
		$form             = $this->get_form();
		$atts             = compact( 'form' );
		return apply_filters( 'frm_process_honeypot', $is_honeypot_spam, $atts );
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

	public function render_field() {
		$honeypot = $this->check_honeypot_setting();
		$form     = $this->get_form();
		?>
			<div class="frm_verify" <?php echo in_array( $honeypot, array( true, 'strict' ), true ) ? '' : 'aria-hidden="true"'; ?>>
				<label for="frm_email_<?php echo esc_attr( $form->id ); ?>">
					<?php esc_html_e( 'If you are human, leave this field blank.', 'formidable' ); ?>
				</label>
				<input type="<?php echo esc_attr( 'strict' === $honeypot ? 'email' : 'text' ); ?>" class="frm_verify" id="frm_email_<?php echo esc_attr( $form->id ); ?>" name="frm_verify" value="<?php echo esc_attr( FrmAppHelper::get_param( 'frm_verify', '', 'get', 'wp_kses_post' ) ); ?>" <?php FrmFormsHelper::maybe_hide_inline(); ?> />
			</div>
		<?php
	}
}
