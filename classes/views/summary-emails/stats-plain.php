<?php
/**
 * Template for plain text stats email
 *
 * @since 6.7
 * @package Formidable
 *
 * @var array $args Content args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

echo esc_html( $args['subject'] );

echo "\r\n\r\n";

echo esc_html( FrmAppHelper::get_formatted_time( $args['from_date'] ) ) . ' - ' . esc_html( FrmAppHelper::get_formatted_time( $args['to_date'] ) ) . ' - ' . esc_url( $args['site_url'] );

echo "\r\n\r\n";

esc_html_e( 'Statistics', 'formidable' );

echo "\r\n\r\n";

foreach ( $args['stats'] as $key => $stat ) {
	echo esc_html( $stat['label'] ) . ': ' . ( isset( $stat['display'] ) ? esc_html( $stat['display'] ) : intval( $stat['count'] ) ) . "\r\n";
}

echo "\r\n";

if ( ! empty( $args['dashboard_url'] ) ) {
	esc_html_e( 'Go to Dashboard:', 'formidable' );
	echo ' ' . esc_url_raw( $args['dashboard_url'] ) . "\r\n\r\n";
}

if ( $args['top_forms'] ) {
	echo esc_html( $args['top_forms_label'] );

	echo "\r\n\r\n";

	foreach ( $args['top_forms'] as $index => $top_form ) {
		echo esc_html( $top_form->form_name ) . ': ';
		// translators: submission count.
		printf( esc_html( _n( '%s submission', '%s submissions', $top_form->items_count, 'formidable' ) ), intval( number_format_i18n( $top_form->items_count ) ) );
		echo "\r\n";
	}

	echo "\r\n";
}

if ( ! empty( $args['out_of_date_plugins'] ) ) {
	printf(
		// translators: the list of out-of-date plugins.
		esc_html__( 'The following plugins are out of date: %s', 'formidable' ),
		esc_html( implode( ', ', $args['out_of_date_plugins'] ) )
	);
	echo "\r\n";
	esc_html_e( 'Please go to your Plugins page to update them.', 'formidable' );
	echo "\r\n\r\n";
}
