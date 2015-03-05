<?php
if ( ! isset( $entry) ) {
    $entry = $record;
} ?>

<div class="misc-pub-section curtime misc-pub-curtime">
    <span id="timestamp">
    <?php
    $datef = __( 'M j, Y @ G:i' );
    printf(__( 'Published on: <b>%1$s</b>'), date_i18n( $datef, strtotime( $entry->created_at ) )); ?>
    </span>
</div>
<?php if ( $entry->updated_at && $entry->updated_at != $entry->created_at ) { ?>
<div class="misc-pub-section curtime misc-pub-curtime">
    <span id="timestamp">
    <?php printf(__( 'Updated on: <b>%1$s</b>', 'formidable' ), date_i18n( $datef, strtotime( $entry->updated_at ) )); ?>
    </span>
</div>
<?php } ?>

<?php do_action('frm_entry_shared_sidebar', $entry); ?>
