<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmHoneypot {

	/**
	 * @var int $form_id
	 */
	private $form_id;

	/**
	 * @var object $form
	 */
	private $form;

	public function __construct( $form_id ) {
		$this->form_id = $form_id;
	}

	private function get_form() {
		if ( ! isset( $this->form ) ) {
			$this->form = FrmForm::getOne( $this->form_id );
		}
		return $this->form;
	}

	public function validate() {
		if ( ! $this->honeypot_option_is_on() || ! $this->check_honeypot_filter() ) {
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
	 * @return bool
	 */
	public function honeypot_option_is_on() {
		$form = $this->get_form();
		return empty( $form->options['no_honeypot'] );
	}

	/**
	 * @return mixed either 'strict', true, or false.
	 */
	private function check_honeypot_filter() {
		$form = $this->get_form();
		return apply_filters( 'frm_run_honeypot', true, compact( 'form' ) );
	}

	public function maybe_render_field() {
		if ( ! $this->honeypot_option_is_on() ) {
			return;
		}

		$honeypot = $this->check_honeypot_filter();
		if ( ! $honeypot ) {
			return;
		}

		$form = $this->get_form();
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
