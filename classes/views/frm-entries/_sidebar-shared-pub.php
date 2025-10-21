<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! isset( $entry ) ) {
	$entry = $record;
}
?>

<div class="misc-pub-section">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_calendar_icon', array( 'aria-hidden' => 'true' ) ); ?>
	<span id="timestamp">
	<?php

	$date_format = get_option( 'date_format' );
	if ( $date_format ) {
		// Use short months since the sidebar space is limited.
		$date_format = str_replace( 'F', 'M', $date_format );
	} else {
		// Fallback if there is no option in the database.
		$date_format = __( 'M j, Y', 'formidable' );
	}

	/**
	 * @since 6.25
	 *
	 * @param string   $text
	 * @param stdClass $entry
	 */
	$additional_timestamp_text = apply_filters( 'frm_additional_timestamp_text', '', $entry );

	printf(
		/* translators: %1$s: Entry status, %2$s: <b> open tag, %3$s: The date, %4$s: Possible additional text, %5$s: </b> close tag */
		esc_html__( '%1$s: %2$s%3$s%4$s%5$s', 'formidable' ),
		esc_html( FrmEntriesHelper::get_entry_status_label( $entry->is_draft ) ),
		'<b>',
		esc_html( FrmAppHelper::get_formatted_time( $entry->created_at, $date_format ) ),
		esc_html( $additional_timestamp_text ),
		'</b>'
	);
	?>
	</span>
</div>

<?php if ( $entry->updated_at && $entry->updated_at != $entry->created_at ) { ?>
<div class="misc-pub-section">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_calendar_icon', array( 'aria-hidden' => 'true' ) ); ?>
	<span id="timestamp">
	<?php
	printf(
		/* translators: %1$s: The date */
		esc_html__( 'Updated: %1$s', 'formidable' ),
		'<b>' . esc_html( FrmAppHelper::get_formatted_time( $entry->updated_at, $date_format ) ) . '</b>'
	);
	?>
	</span>
</div>
<?php } ?>

<?php do_action( 'frm_entry_shared_sidebar', $entry ); ?>
