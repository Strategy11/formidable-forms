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

FrmEmailSummaryHelper::plain_text_echo( $args['subject'] );

echo "\r\n\r\n";

FrmEmailSummaryHelper::plain_text_echo( FrmAppHelper::get_formatted_time( $args['from_date'] ) . ' - ' . FrmAppHelper::get_formatted_time( $args['to_date'] ) . ' - ' . esc_url_raw( $args['site_url'] ) );

echo "\r\n\r\n";

FrmEmailSummaryHelper::plain_text_echo( __( 'Statistics', 'formidable' ) );

echo "\r\n\r\n";

foreach ( $args['stats'] as $stat ) {
	FrmEmailSummaryHelper::plain_text_echo( $stat['label'] . ': ' . ( isset( $stat['display'] ) ? $stat['display'] : intval( $stat['count'] ) ) . "\r\n" );
}

echo "\r\n";

if ( ! empty( $args['dashboard_url'] ) ) {
	FrmEmailSummaryHelper::plain_text_echo( __( 'Go to Dashboard:', 'formidable' ) );
	echo ' ' . esc_url_raw( $args['dashboard_url'] ) . "\r\n\r\n";
}

if ( $args['top_forms'] ) {
	FrmEmailSummaryHelper::plain_text_echo( $args['top_forms_label'] );

	echo "\r\n\r\n";

	foreach ( $args['top_forms'] as $top_form ) {
		FrmEmailSummaryHelper::plain_text_echo( $top_form->form_name );
		echo ': ';

		// translators: submission count.
		$submissions_count = sprintf( _n( '%s submission', '%s submissions', $top_form->items_count, 'formidable' ), intval( number_format_i18n( $top_form->items_count ) ) );
		FrmEmailSummaryHelper::plain_text_echo( $submissions_count );
		unset( $submissions_count );
		echo "\r\n";
	}

	echo "\r\n";
}

if ( ! empty( $args['out_of_date_plugins'] ) ) {
	FrmEmailSummaryHelper::plain_text_echo(
		sprintf(
			// translators: the list of out-of-date plugins.
			__( 'The following plugins are out of date: %s', 'formidable' ),
			implode( ', ', $args['out_of_date_plugins'] )
		)
	);
	echo "\r\n";
	FrmEmailSummaryHelper::plain_text_echo( __( 'Please go to your Plugins page to update them.', 'formidable' ) );
	echo "\r\n\r\n";
}
