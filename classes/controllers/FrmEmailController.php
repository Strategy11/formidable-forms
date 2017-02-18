<?php

/**
 * @since 2.03.04
 */
class FrmEmailController {

	/**
	 * FrmEmailController constructor.
	 *
	 * @since 2.03.04
	 */
	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}
		add_action( 'frm_trigger_email_action', 'FrmEmailController::trigger_email', 10, 3 );
	}

	/**
	 * Trigger an email action
	 *
	 * @param object $action
	 * @param object $entry
	 * @param object $form
	 */
	public static function trigger_email( $action, $entry, $form ) {
		$email = new FrmEmail( $action, $entry, $form );

		if ( ! $email->should_send() ) {
			return;
		}

		$sent = $email->send();

		if ( $sent ) {
			self::print_emails_sent( $email->package_atts() );
		}
	}

	/**
	 * Print the recipients
	 *
	 * @since 2.03.04
	 *
	 * @param array $atts
	 */
	private static function print_emails_sent( $atts ) {
		if ( apply_filters( 'frm_echo_emails', false ) ) {

			$sent_to = array_merge( (array) $atts[ 'to_email' ], (array) $atts[ 'cc' ], (array) $atts[ 'bcc' ] );
			$sent_to = array_filter( $sent_to );

			$temp = str_replace( '<', '&lt;', $sent_to );
			echo ' ' . FrmAppHelper::kses( implode( ', ', (array) $temp ) );
		}
	}

	/**
	 * This function should only be fired when Mandrill is sending an HTML email
	 * This will make sure Mandrill doesn't mess with our HTML emails
	 *
	 * @since 2.03.04
	 */
	public static function remove_mandrill_br() {
		return false;
	}

}