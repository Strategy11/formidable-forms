<?php
/**
 * Template for plain text stats email
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

echo esc_html( $args['subject'] );

echo "\r\n\r\n";

echo esc_html( $args['from_date'] ) . ' - ' . esc_html( $args['to_date'] ) . ' - ' . esc_url( $args['site_url'] );

echo "\r\n\r\n";

esc_html_e( 'Statistics', 'formidable' );

echo "\r\n\r\n";

foreach ( $args['stats'] as $key => $stat ) {
	echo esc_html( $stat['label'] ) . ': ' . intval( $stat['count'] ) . "\r\n";
}

if ( ! empty( $args['dashboard_url'] ) ) {
	esc_html_e( 'Go to Dashboard:', 'formidable' ) . ' ' . esc_url( $args['dashboard_url'] ) . "\r\n";
}

echo "\r\n";

echo esc_html( $args['top_forms_label'] );

echo "\r\n\r\n";

foreach ( $args['top_forms'] as $index => $top_form ) {
	echo esc_html( $top_form->form_name ) . ': ';
	printf( esc_html( _n( 'submission', 'submissions', $top_form->items_count, 'formidable' ) ), number_format_i18n( $top_form->items_count ) );
	echo "\r\n";
}

if ( ! empty( $args['out_of_date_plugins'] ) ) {
	echo "\r\n";
	esc_html_e( 'Following plugins are out of date:', 'formidable' ) . ' ' . esc_html( implode( ', ', $args['out_of_date_plugins'] ) );
}
