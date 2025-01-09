<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.03.04
 */
class FrmEmailHelper {

	/**
	 * Get the userID field from a form
	 * This will not get repeating or embedded userID fields
	 *
	 * @since 2.03.04
	 *
	 * @param int $form_id
	 *
	 * @return int
	 */
	public static function get_user_id_field_for_form( $form_id ) {
		$where = array(
			'type'    => 'user_id',
			'form_id' => $form_id,
		);

		$user_id_field = FrmDb::get_var( 'frm_fields', $where, 'id' );

		return (int) $user_id_field;
	}

	/**
	 * This function should only be fired when Mandrill is sending an HTML email
	 * This will make sure Mandrill doesn't mess with our HTML emails
	 *
	 * @since 2.03.04
	 *
	 * @return bool
	 */
	public static function remove_mandrill_br() {
		return false;
	}

	/**
	 * Gets default from email address in header for emails.
	 *
	 * @since 6.15
	 *
	 * @return string
	 */
	public static function get_default_from_email() {
		$settings = FrmAppHelper::get_settings();
		if ( $settings->from_email && is_email( $settings->from_email ) ) {
			return $settings->from_email;
		}
		return get_option( 'admin_email' );
	}
}
