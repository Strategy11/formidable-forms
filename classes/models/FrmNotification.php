<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmNotification {
	public function __construct() {
		self::hook_emails_to_action();
	}

	/**
	 * Trigger an email action
	 *
	 * @param object $action
	 * @param object $entry
	 * @param object $form
	 *
	 * @return void
	 */
	public static function trigger_email( $action, $entry, $form ) {
		$email = new FrmEmail( $action, $entry, $form );

		if ( ! $email->should_send() ) {
			return;
		}

		$sent = $email->send();

		if ( $sent ) {
			self::print_recipients( $email->package_atts() );
		}
	}

	/**
	 * Remove the trigger_email function from the frm_trigger_email_action hook
	 *
	 * @since 2.03.04
	 *
	 * @return void
	 */
	public static function stop_emails() {
		remove_action( 'frm_trigger_email_action', 'FrmNotification::trigger_email', 10 );
	}

	/**
	 * Hook the trigger_email function to frm_trigger_email_action action
	 *
	 * @since 2.03.04
	 *
	 * @return void
	 */
	public static function hook_emails_to_action() {
		add_action( 'frm_trigger_email_action', 'FrmNotification::trigger_email', 10, 3 );
	}

	/**
	 * Print the recipients
	 *
	 * @since 2.03.04
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	private static function print_recipients( $atts ) {
		if ( apply_filters( 'frm_echo_emails', false ) ) {

			$sent_to = array_merge( (array) $atts['to_email'], (array) $atts['cc'], (array) $atts['bcc'] );
			$sent_to = array_filter( $sent_to );

			$temp = str_replace( '<', '&lt;', $sent_to );
			echo ' ' . FrmAppHelper::kses( implode( ', ', (array) $temp ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
