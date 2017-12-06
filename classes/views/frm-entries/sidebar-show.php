<div id="postbox-container-1" class="postbox-container frm-right-panel">
<div id="submitdiv" class="postbox frm-no-border frm_no_print">
    <div class="inside">
        <div class="submitbox">
			<div id="major-publishing-actions">
				<div class="alignleft">
					<?php FrmEntriesHelper::actions_dropdown( compact( 'id', 'entry' ) ); ?>
				</div>
				<?php do_action( 'frm_entry_major_pub', $entry ); ?>
				<div class="clear"></div>
			</div>
			<?php if ( has_action( 'frm_show_entry_publish_box' ) ) { ?>
				<div id="minor-publishing" class="frm_remove_border">
					<div class="misc-pub-section">
						<?php do_action( 'frm_show_entry_publish_box', $entry ); ?>
						<div class="clear"></div>
					</div>
				</div>
			<?php } ?>
        </div>
    </div>
</div>
<?php
do_action( 'frm_show_entry_sidebar', $entry );
FrmEntriesController::entry_sidebar( $entry );
?>
</div>
