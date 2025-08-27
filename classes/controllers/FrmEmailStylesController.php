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

/**
 * Class FrmEmailStylesController
 */
class FrmEmailStylesController {

	/**
	 * Gets email styles.
	 *
	 * @return array[]
	 */
	public static function get_email_styles() {
		$email_styles = array(
			'classic' => array(
				'name'          => __( 'Classic', 'formidable' ),
				'selectable'    => true,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/classic.svg',
				'is_plain_text' => false,
			),
			'plain'   => array(
				'name'          => __( 'Plain Text', 'formidable' ),
				'selectable'    => true,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/plain.svg',
				'is_plain_text' => true,
			),
			'modern'  => array(
				'name'          => __( 'Modern', 'formidable' ),
				'selectable'    => false,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/modern.svg',
				'is_plain_text' => false,
			),
			'sleek'   => array(
				'name' => __( 'Sleek', 'formidable' ),
				'selectable'    => false,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/sleek.svg',
				'is_plain_text' => false,
			),
			'compact' => array(
				'name'          => __( 'Compact', 'formidable' ),
				'selectable'    => false,
				'icon_url'      => FrmAppHelper::plugin_url() . '/images/email-styles/compact.svg',
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

	/**
	 * Gets email style preview URL.
	 *
	 * @param string $style_key Style key.
	 * @return string
	 */
	public static function get_email_style_preview_url( $style_key ) {
		return wp_nonce_url( admin_url( 'admin-ajax.php?action=frm_email_style_preview&style_key=' . $style_key ), 'frm_email_style_preview' );
	}

	/**
	 * Gets the test email content.
	 *
	 * @param string $style_key Default is `false`, using the one in settings.
	 * @return string
	 */
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
				'value' => 'Lorem ipsum dolor sit amet, <a href="#">consectetur adipiscing elit</a>.
							Praesent in risus velit. Donec molestie tincidunt ex sed consequat. Ut ornare fringilla fringilla.',
			),
		);

		if ( 'plain' !== $style_key ) {
			$style_settings = self::get_email_style_settings();

			// Sleek table style doesn't have any border.
			$should_remove_border = 'sleek' === $style_key;

			// Modern and Compact table styles don't have top and bottom border.
			$should_remove_top_bottom_border = 'classic' !== $style_key;

			$table_generator = self::get_table_generator( $style_key );

			$content = $table_generator->generate_table_header();

			// By default, table has the bottom border and table cells have top border.
			if ( $should_remove_top_bottom_border ) {
				$content = $table_generator->remove_border( $content, 'bottom' );
			}

			foreach ( $table_rows as $index => $row ) {
				if ( 'compact' === $style_key ) {
					// Compact table has one column layout.
					$table_row = $table_generator->generate_two_cell_table_row( $row['label'], $row['value'] );
				} else {
					// Other table styles have two columns layout.
					$row_html  = '<div style="font-weight:600;">' . $row['label'] . '</div>';
					$row_html .= $row['value'];
					$table_row = $table_generator->generate_single_cell_table_row( $row_html );
				}

				if ( ! $index && $should_remove_top_bottom_border ) {
					$table_row = $table_generator->remove_border( $table_row );
				}

				$content .= $table_row;
			}

			$content .= $table_generator->generate_table_footer();

			if ( $should_remove_border ) {
				$content = $table_generator->remove_border( $content, 'top' );
			}

			if ( 'classic' !== $style_key ) {
				$content = self::wrap_email_message( $content );
			}

			$wrapped_content = '<html><head><meta charset="utf-8" /></head>';
			if ( 'classic' !== $style_key ) {
				$wrapped_content .= '<style>
						body {background-color:' . esc_attr( $style_settings['bg_color'] ) . ';}
						a {color:' . esc_attr( $style_settings['link_color'] ) . ';}
					</style>';
			}
			$wrapped_content .= '</head><body>' . $content . '</body></html>';

			$content = $wrapped_content;
		} else {
			$content = '';
			foreach ( $table_rows as $row ) {
				$content .= $row['label'] . ': ' . $row['value'] . "\r\n";
			}
		}//end if

		return $content;
	}

	/**
	 * Gets table generator object for an email style.
	 *
	 * @param string $email_style Email style. Default is `false`: using the one in global settings.
	 * @return FrmTableHTMLGenerator
	 */
	public static function get_table_generator( $email_style = false ) {
		if ( false === $email_style ) {
			$email_style = self::get_email_style();
		}

		$style_settings = self::get_email_style_settings();

		$atts = array(
			'inline_style' => true,
			'email_style'  => $email_style,
		);

		if ( 'classic' !== $email_style ) {
			$atts['border_color'] = $style_settings['border_color'];
			$atts['cell_padding'] = '16px 0';
			$atts['bg_color']     = $style_settings['container_bg_color'];
			$atts['alt_bg_color'] = $style_settings['container_bg_color'];
			$atts['text_color']   = $style_settings['text_color'];
		}

		return new FrmTableHTMLGenerator( 'entry', $atts );
	}

	/**
	 * AJAX handler for previewing email style.
	 */
	public static function ajax_preview() {
		// Check permission and nonce
		FrmAppHelper::permission_check( 'manage_options' );
		check_ajax_referer( 'frm_email_style_preview' );

		$style_key     = FrmAppHelper::get_param( 'style_key', '', 'sanitize_text_field' );
		$not_exist_msg = __( "This email style doesn't exist", 'formidable' );
		if ( ! $style_key ) {
			die( esc_html( $not_exist_msg ) );
		}

		$styles = self::get_email_styles();
		if ( ! isset( $styles[ $style_key ] ) ) {
			die( esc_html( $not_exist_msg ) );
		}

		$style_key = FrmAppHelper::get_param( 'style_key', '', 'sanitize_text_field' );
		$content   = self::get_test_email_content( $style_key );

		if ( 'plain' !== $style_key ) {
			header( 'Content-Type: text/html; charset=utf-8' );
		} else {
			header( 'Content-Type: text/plain' );
		}

		echo wp_kses_post( $content );
		die();
	}

	/**
	 * Gets selected email style in global settings.
	 *
	 * @return string
	 */
	public static function get_email_style() {
		$frm_settings = FrmAppHelper::get_settings();
		return ! empty( $frm_settings->email_style ) ? $frm_settings->email_style : 'classic';
	}

	/**
	 * AJAX handler for sending a test email.
	 */
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

	/**
	 * Shows placeholder Pro settings.
	 */
	public static function show_upsell_settings() {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-settings/email/settings.php';
	}

	/**
	 * Gets email style settings value.
	 *
	 * @return array
	 */
	public static function get_email_style_settings() {
		/**
		 * Filter the email style settings value.
		 *
		 * @since x.x
		 *
		 * @param array $settings The settings value.
		 */
		return apply_filters(
			'frm_email_style_settings',
			array(
				// Placeholder image.
				'img'                => FrmAppHelper::plugin_url() . '/images/email-styles/placeholder.png',
				'img_size'           => '',
				'img_align'          => '',
				'img_location'       => '',
				'bg_color'           => '#EAECF0',
				'container_bg_color' => '#ffffff',
				'text_color'         => '#475467',
				'link_color'         => '#4199FD',
				'border_color'       => '#dddddd',
				'font'               => '',
			)
		);
	}

	/**
	 * Wraps email message with new settings.
	 *
	 * @param string $message Email message.
	 * @return string
	 */
	public static function wrap_email_message( $message ) {
		$style_settings = self::get_email_style_settings();

		$header_img = '';
		if ( $style_settings['img'] ) {
			$img_align = $style_settings['img_align'] ? $style_settings['img_align'] : 'center';
			$img_size  = $style_settings['img_size'] ? $style_settings['img_size'] : 'thumbnail';
			$img_url   = is_numeric( $style_settings['img'] ) ? wp_get_attachment_image_url( $style_settings['img'], $img_size ) : $style_settings['img'];

			$header_img .= sprintf(
				'<div style="text-align:%s;margin-bottom:32px;">',
				esc_attr( $img_align )
			);

			$header_img .= sprintf(
				'<img src="%s" alt="" />',
				esc_url( $img_url )
			);

			$header_img .= '</div>';
		}

		// Wrapper.
		$font_family = $style_settings['font'] ? $style_settings['font'] : 'Inter,sans-serif';
		$new_message = sprintf(
			'<div style="background-color:%1$s;color:%2$s;font-family:%3$s;padding:40px 0;">',
			esc_attr( $style_settings['bg_color'] ),
			esc_attr( $style_settings['text_color'] ),
			esc_attr( $font_family )
		);

		// Container.
		$new_message .= '<div style="width:640px;margin:auto;">';

		// Header image if outside.
		if ( $style_settings['img'] && 'inside' !== $style_settings['img_location'] ) {
			$new_message .= $header_img;
		}

		// Main container.
		$new_message .= sprintf(
			'<div style="background-color:%s;border-radius:8px;padding:32px;">',
			esc_attr( $style_settings['container_bg_color'] )
		);

		// Header image if inside.
		if ( $style_settings['img'] && 'inside' === $style_settings['img_location'] ) {
			$new_message .= $header_img;
		}

		// The message.
		$new_message .= self::add_inline_css( 'a', 'color:' . $style_settings['link_color'] . ';', $message );

		$new_message .= '</div></div></div>';

		return $new_message;
	}

	/**
	 * Adds inline CSS to a tag in the content.
	 *
	 * @param string $tag     Tag name.
	 * @param string $css     CSS code.
	 * @param string $content The content.
	 * @return string
	 */
	private static function add_inline_css( $tag, $css, $content ) {
		$regex = '/<' . $tag . '.*?>/msi';
		preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$searches = array();
		$replaces = array();

		foreach ( $matches as $match ) {
			$searches[] = $match[0];
			$replaces[] = self::add_css_to_style_attr( $tag, $css, $match[0] );
		}

		return str_replace( $searches, $replaces, $content );
	}

	/**
	 * Adds inline CSS to a single HTML tag.
	 *
	 * @param string $tag  Tag name.
	 * @param string $css  CSS code.
	 * @param string $html The HTML tag.
	 * @return string
	 */
	private static function add_css_to_style_attr( $tag, $css, $html ) {
		$regex = '/\sstyle=("|\')/mi';
		if ( preg_match( $regex, $html, $matches ) ) {
			$search  = $matches[0];
			$replace = $search . $css;
			return preg_replace( $regex, $replace, $html );
		}

		return str_replace( '<' . $tag, '<' . $tag . ' style="' . $css . '"', $html );
	}
}
