<?php
if ( ! isset( $entry ) ) {
    $entry = $record;
} ?>

<div class="misc-pub-section curtime misc-pub-curtime">
    <span id="timestamp">
    <?php
    $date_format = __( 'M j, Y @ G:i' );
	printf( esc_html__( 'Published on: %1$s' ), '<b>' . esc_html( FrmAppHelper::get_localized_date( $date_format, $entry->created_at ) ) . '</b>' );
	?>
    </span>
</div>
<?php if ( FrmAppHelper::get_param( 'frm_action' ) == 'show' ) { ?>
<div class="misc-pub-section frm_no_print">
	<span class="dashicons dashicons-format-aside wp-media-buttons-icon"></span>
	<a href="#" onclick="window.print();return false;"><?php esc_html_e( 'Print', 'formidable' ) ?></a>
</div>
<?php } ?>
<?php if ( $entry->updated_at && $entry->updated_at != $entry->created_at ) { ?>
<div class="misc-pub-section curtime misc-pub-curtime">
    <span id="timestamp">
	<?php printf( esc_html__( 'Updated on: %1$s', 'formidable' ), '<b>' . esc_html( FrmAppHelper::get_localized_date( $date_format, $entry->updated_at ) ) . '</b>' ); ?>
    </span>
</div>
<?php } ?>

<?php do_action( 'frm_entry_shared_sidebar', $entry ); ?>
