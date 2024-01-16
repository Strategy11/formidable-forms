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

$frm_settings = FrmAppHelper::get_settings();

echo '%%INNER_CONTENT%%';

// translators: contact support URL.
printf( esc_html__( 'Need help? Get in touch with our team at %s', 'formidable' ), esc_url_raw( $args['support_url'] ) );

echo "\r\n\r\n";

// translators: Unsubscribe URL.
printf( esc_html__( 'Unsubscribe: %s', 'formidable' ), esc_url_raw( $args['unsubscribe_url'] ) );
