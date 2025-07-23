<?php
/**
 * Controller for email styles
 *
 * @since x.x
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEmailStylesController {

	public static function get_email_styles() {
		$email_styles = array(
			'classic' => array(
				'name'          => __( 'Classic', 'formidable' ),
				'selectable'    => true,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/classic.svg',
				'is_plain_text' => false,
			),
			'plain' => array(
				'name'          => __( 'Plain Text', 'formidable' ),
				'selectable'    => true,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/plain.svg',
				'is_plain_text' => true,
			),
			'modern' => array(
				'name' => __( 'Modern', 'formidable' ),
				'selectable' => false,
				'icon_url'   => FrmAppHelper::plugin_url() . '/images/email-styles/modern.svg',
				'is_plain_text' => false,
			),
			'sleek' => array(
				'name' => __( 'Sleek', 'formidable' ),
				'selectable' => false,
				'icon_url'   => FrmAppHelper::plugin_url() . '/images/email-styles/sleek.svg',
				'is_plain_text' => false,
			),
			'compact' => array(
				'name' => __( 'Compact', 'formidable' ),
				'selectable' => false,
				'icon_url'   => FrmAppHelper::plugin_url() . '/images/email-styles/compact.svg',
				'is_plain_text' => false,
			),
		);

		/**
		 * Filter the email styles.
		 *
		 * @since x.x
		 *
		 * @param array[] $email_styles The email styles.
		 * @return array
		 */
		return apply_filters( 'frm_email_styles', $email_styles );
	}

	public static function get_email_style_preview_url( $style_key ) {
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_email_style_preview&style_key=' . $style_key ) );
	}
}
