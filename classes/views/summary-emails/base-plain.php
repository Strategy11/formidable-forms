<?php
/**
 * Base template for summary emails
 *
 * @since 6.7
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- don't need to escape for plain-text email.
$frm_settings = FrmAppHelper::get_settings();

echo '%%INNER_CONTENT%%';

// translators: contact support URL.
printf( __( 'Need help? Get in touch with our team at %s', 'formidable' ), esc_url_raw( $args['support_url'] ) );

echo "\r\n\r\n";

// translators: Unsubscribe URL.
printf( __( 'Unsubscribe: %s', 'formidable' ), esc_url_raw( $args['unsubscribe_url'] ) );
//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
