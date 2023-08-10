<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! isset( $entry ) ) {
	$entry = $record;
} ?>

<div class="misc-pub-section">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_calendar_icon', array( 'aria-hidden' => 'true' ) ); ?>
	<span id="timestamp">
	<?php
	$date_format = __( 'M j, Y @ G:i', 'formidable' );
	printf(
		/* translators: %1$s: Entry status %2$s: The date */
		esc_html__( '%1$s: %2$s', 'formidable' ),
		esc_html( FrmEntriesHelper::get_entry_status_label( $entry->is_draft ) ),
		'<b>' . esc_html( FrmAppHelper::get_localized_date( $date_format, $entry->created_at ) ) . '</b>'
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
		'<b>' . esc_html( FrmAppHelper::get_localized_date( $date_format, $entry->updated_at ) ) . '</b>'
	);
	?>
	</span>
</div>
<?php } ?>

<?php do_action( 'frm_entry_shared_sidebar', $entry ); ?>
