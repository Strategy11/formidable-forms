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

	private static function get_fake_entry() {
		$entry             = new stdClass();
		$entry->post_id    = 0;
		$entry->id         = 0;
		$entry->ip         = '';
		$entry->form_id    = 1;
		$entry->metas      = array();
		$entry->user_id    = get_current_user_id();
		$entry->updated_by = 0;

		$entry->item_meta = array(
			2 => 'John',
			3 => 'Doe',
			4 => 'john@doe.com',
			5 => 'Contact subject',
			6 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent in risus velit. Donec molestie tincidunt ex sed consequat. Ut ornare fringilla fringilla.',
		);

		return $entry;
	}

	public static function ajax_preview() {
		check_ajax_referer( 'frm_email_style_preview' );

		$style_key = FrmAppHelper::get_param( 'style_key', '', 'sanitize_text_field' );
		$not_exist_msg = __( "This email style doesn't exist", 'formidable' );
		if ( ! $style_key ) {
			die( $not_exist_msg);
		}

		$styles = self::get_email_styles();
		if ( ! isset( $styles[ $style_key ] ) ) {
			die( $not_exist_msg );
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
			$atts            = array(
				'inline_style' => true,
			);
			$table_generator = new FrmTableHTMLGenerator( 'entry', $atts );

			$content = $table_generator->generate_table_header();
			foreach ( $table_rows as $row ) {
				$content .= $table_generator->generate_two_cell_table_row( $row['label'], $row['value'] );
			}
			$content .= $table_generator->generate_table_footer();

			$content = '<div style="width:640px;">' . $content . '</div>';
			header( 'Content-Type: text/html; charset=utf-8' );
		} else {
			$content = '';
			foreach ( $table_rows as $row ) {
				$content .= $row['label'] . ': ' . $row['value'] . "\r\n";
			}
			header( 'Content-Type: text/plain' );
		}

		echo $content;
		die();
	}
}
