<div id="postbox-container-1" class="postbox-container frm_no_print frm-right-panel">
<div id="submitdiv" class="postbox">
    <div class="inside">
        <div class="submitbox">
			<div id="major-publishing-actions">
				<?php if ( current_user_can('frm_delete_entries') ) { ?>
					<div id="delete-action">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=destroy&id=' . $id . '&form=' . $entry->form_id ) ) ?>" class="submitdelete deletion" data-frmverify="<?php esc_attr_e( 'Are you sure?', 'formidable' ) ?>" title="<?php esc_attr_e( 'Delete' ) ?>">
							<?php _e( 'Delete' ) ?>
						</a>
						<?php if ( ! empty( $entry->post_id ) ) { ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=destroy&id=' . $id . '&form=' . $entry->form_id . '&keep_post=1' ) ) ?>" class="submitdelete deletion frm_delete_wo_post" data-frmverify="<?php esc_attr_e( 'Are you sure?', 'formidable' ) ?>" title="<?php esc_attr_e( 'Delete entry but leave the post', 'formidable' ) ?>">
								<?php _e( 'Delete without Post', 'formidable' ) ?>
							</a>
						<?php } ?>
					</div>
				<?php } ?>

				<?php do_action( 'frm_entry_major_pub', $entry ); ?>
				<div class="clear"></div>
			</div>
			<?php if ( has_action('frm_show_entry_publish_box') ) { ?>
				<div id="minor-publishing" class="frm_remove_border">
					<div class="misc-pub-section">
						<?php do_action('frm_show_entry_publish_box', $entry); ?>
						<div class="clear"></div>
					</div>
				</div>
			<?php } ?>
        </div>
    </div>
</div>
<?php do_action('frm_show_entry_sidebar', $entry);
FrmEntriesController::entry_sidebar($entry);
?>
</div>
