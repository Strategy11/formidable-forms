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

echo $args['subject'];

echo "\r\n\r\n";

echo FrmAppHelper::get_formatted_time( $args['from_date'] ) . ' - ' . FrmAppHelper::get_formatted_time( $args['to_date'] ) . ' - ' . esc_url_raw( $args['site_url'] );

echo "\r\n\r\n";

_e( 'Statistics', 'formidable' );

echo "\r\n\r\n";

foreach ( $args['stats'] as $key => $stat ) {
	echo $stat['label'] . ': ' . ( isset( $stat['display'] ) ? $stat['display'] : intval( $stat['count'] ) ) . "\r\n";
}

echo "\r\n";

if ( ! empty( $args['dashboard_url'] ) ) {
	_e( 'Go to Dashboard:', 'formidable' );
	echo ' ' . esc_url_raw( $args['dashboard_url'] ) . "\r\n\r\n";
}

if ( $args['top_forms'] ) {
	echo $args['top_forms_label'];

	echo "\r\n\r\n";

	foreach ( $args['top_forms'] as $index => $top_form ) {
		echo wp_unslash( $top_form->form_name ) . ': ';
		// translators: submission count.
		printf( _n( '%s submission', '%s submissions', $top_form->items_count, 'formidable' ), intval( number_format_i18n( $top_form->items_count ) ) );
		echo "\r\n";
	}

	echo "\r\n";
}

if ( ! empty( $args['out_of_date_plugins'] ) ) {
	printf(
		// translators: the list of out-of-date plugins.
		__( 'The following plugins are out of date: %s', 'formidable' ),
		implode( ', ', $args['out_of_date_plugins'] )
	);
	echo "\r\n";
	_e( 'Please go to your Plugins page to update them.', 'formidable' );
	echo "\r\n\r\n";
}
