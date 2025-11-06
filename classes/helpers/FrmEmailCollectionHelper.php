<?php
/**
 * Email Collection Helper class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Provides helper functions for email collection and subscription.
 *
 * @since 6.25
 */
class FrmEmailCollectionHelper {

	/**
	 * When the user consents to receiving news of updates, subscribe their email to ActiveCampaign.
	 *
	 * @since 6.25
	 *
	 * @param string $email The email address to subscribe to ActiveCampaign.
	 * @return void
	 */
	public static function subscribe_to_active_campaign( $email = '' ) {
		$user = wp_get_current_user();
		if ( ! $email ) {
			$email = $user->user_email;
		}

		if ( self::is_fake_email( $email ) ) {
			return;
		}

		$user_id    = $user->ID;
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name  = get_user_meta( $user_id, 'last_name', true );

		wp_remote_post(
			'https://sandbox.formidableforms.com/api/wp-admin/admin-ajax.php?action=frm_forms_preview&form=subscribe-onboarding',
			array(
				'body' => http_build_query(
					array(
						'form_key'      => 'subscribe-onboarding',
						'frm_action'    => 'create',
						'form_id'       => 5,
						'item_key'      => '',
						'item_meta[0]'  => '',
						'item_meta[15]' => $email,
						'item_meta[17]' => 'Source - FF Lite Plugin Onboarding',
						'item_meta[18]' => is_string( $first_name ) ? $first_name : '',
						'item_meta[19]' => is_string( $last_name ) ? $last_name : '',
					)
				),
			)
		);
	}

	/**
	 * Check if an email is fake, test, or local development email.
	 *
	 * @since 6.25
	 *
	 * @param string $email The email address to check.
	 * @return bool True if the email is fake/test, false if valid.
	 */
	public static function is_fake_email( $email ) {
		if ( ! is_email( $email ) ) {
			return true;
		}

		$substrings = array(
			'@wpengine.local',
			'@example.com',
			'@localhost',
			'@local.dev',
			'@local.test',
			'test@gmail.com',
			'admin@gmail.com',
		);

		foreach ( $substrings as $substring ) {
			if ( false !== strpos( $email, $substring ) ) {
				return true;
			}
		}

		return false;
	}
}
