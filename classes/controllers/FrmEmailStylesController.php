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
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_email_style_preview&style_key=' . $style_key ), 'frm_email_style_preview' );
	}

	private static function get_test_email_content( $style_key = false ) {
		if ( ! $style_key ) {
			$style_key = self::get_email_style();
		}

		$table_rows = array(
			array(
				'label' => 'Name',
				'value' => 'John Doe',
			),
			array(
				'label' => 'Email address',
				'value' => 'john@doe.com',
			),
			array(
				'label' => 'Subject',
				'value' => 'Contact subject',
			),
			array(
				'label' => 'Message',
				'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent in risus velit. Donec molestie tincidunt ex sed consequat. Ut ornare fringilla fringilla.',
			),
		);

		if ( 'plain' !== $style_key ) {
			$atts = array(
				'inline_style' => true,
				'email_style'  => $style_key,
			);

			$should_remove_border            = 'sleek' === $style_key;
			$should_remove_top_bottom_border = 'classic' !== $style_key;

			$table_generator = new FrmTableHTMLGenerator( 'entry', $atts );

			$content = '<div class="frm-email-wrapper" style="width:640px">';

			if ( 'classic' !== $style_key ) {
				$content .= '<div class="frm-email-logo" style="text-align:center;margin-bottom:40px;"><img /></div>';
			}

			$content .= '<div class="frm-email-content" style="padding:40px;border-radius:8px;background-color:#fff;">';

			$content .= $table_generator->generate_table_header();
			if ( $should_remove_top_bottom_border ) {
				$content = $table_generator->remove_border( $content, 'bottom' );
			}

			foreach ( $table_rows as $index => $row ) {
				if ( 'compact' === $style_key ) {
					$table_row = $table_generator->generate_two_cell_table_row( $row['label'], $row['value'] );
				} else {
					$row_html = '<div style="font-weight:bold;">' . $row['label'] . '</div>';
					$row_html .= ( '<div>' . $row['value'] . '</div>' );
					$table_row = $table_generator->generate_single_cell_table_row( $row_html );
				}

				if ( ! $index && $should_remove_top_bottom_border ) {
					$table_row = $table_generator->remove_border( $table_row );
				}

				$content .= $table_row;
			}

			$content .= $table_generator->generate_table_footer();

			$content = $table_generator->remove_border( $content, 'top' );

			$content .= '</div></div>';

			$content = '<html><head><meta charset="utf-8" /><style>body {background-color: #c4c4c4;}</style></head><body>' . $content . '</body>';
		} else {
			$content = '';
			foreach ( $table_rows as $row ) {
				$content .= $row['label'] . ': ' . $row['value'] . "\r\n";
			}
		}

		return $content;
	}

	public static function ajax_preview() {
		// Check permission and nonce
		FrmAppHelper::permission_check( 'manage_options' );
		check_ajax_referer( 'frm_email_style_preview' );

		$style_key     = FrmAppHelper::get_param( 'style_key', '', 'sanitize_text_field' );
		$not_exist_msg = __( "This email style doesn't exist", 'formidable' );
		if ( ! $style_key ) {
			die( $not_exist_msg);
		}

		$styles = self::get_email_styles();
		if ( ! isset( $styles[ $style_key ] ) ) {
			die( $not_exist_msg );
		}

		$style_key = FrmAppHelper::get_param( 'style_key', '', 'sanitize_text_field' );
		$content   = self::get_test_email_content( $style_key );

		if ( 'plain' !== $style_key ) {
			header( 'Content-Type: text/html; charset=utf-8' );
		} else {
			header( 'Content-Type: text/plain' );
		}

		echo $content;
		die();
	}

	public static function get_email_style() {
		$frm_settings = FrmAppHelper::get_settings();
		return ! empty( $frm_settings->email_style ) ? $frm_settings->email_style : 'classic';
	}

	public static function ajax_send_test_email() {
		// Check permission and nonce
		FrmAppHelper::permission_check( 'manage_options' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$emails_str   = FrmAppHelper::get_post_param( 'emails_str', '', 'sanitize_text_field' );
		$emails       = explode( ',', $emails_str );
		$valid_emails = array();
		foreach ( $emails as $email ) {
			$email = trim( $email );
			if ( empty( $email ) || ! is_email( $email ) ) {
				continue;
			}
			$valid_emails[] = $email;
		}

		if ( empty( $valid_emails ) ) {
			wp_send_json_error( __( 'Invalid email address', 'formidable' ) );
		}

		$email_style = self::get_email_style();

		$subject = __( 'Formidable Test Email', 'formidable' );
		$content = self::get_test_email_content();
		$headers = array();

		if ( 'plain' === $email_style ) {
			$headers[] = 'Content-Type: text/plain';
		} else {
			$headers[] = 'Content-Type: text/html; charset=utf-8';
		}

		$result = wp_mail( $valid_emails, $subject, $content, $headers );
		if ( $result ) {
			wp_send_json_success( __( 'Test email sent successfully!', 'formidable' ) );
		}

		wp_send_json_error( __( 'Failed to send test email!', 'formidable' ) );
	}
}
