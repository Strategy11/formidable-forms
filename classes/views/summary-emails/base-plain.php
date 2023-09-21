<?php
/**
 * Base template for summary emails
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings = FrmAppHelper::get_settings();

echo '%%INNER_CONTENT%%';

if ( 'Formidable' === $frm_settings->menu ) {
	echo 'Keep conquering new heights with Formidable Forms. Your progress fuels our passion!' . "\r\n";
	echo 'Strategy11, 12180 S 300 E #785, Draper, UT 84020, United States' . "\r\n";
}

// translators: Unsubscribe URL.
printf( esc_html__( 'Unsubscribe this email at: %s', 'formidable' ), esc_url( $args['unsubscribe_url'] ) );
