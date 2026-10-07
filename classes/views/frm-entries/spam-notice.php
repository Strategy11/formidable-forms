<?php
/**
 * Spam notice and "Not spam" modal shown at the top of a spam entry.
 *
 * @since x.x
 *
 * @package Formidable
 *
 * @var stdClass  $entry           The spam entry.
 * @var stdClass  $form            The entry's form.
 * @var string    $source_label    The spam check that flagged the entry. Empty when unknown.
 * @var bool      $manual_spam     Whether the entry was manually marked as spam.
 * @var bool      $can_moderate    Whether the current user can mark the entry as not spam.
 * @var WP_Post[] $pending_actions Create actions that can run when the entry is marked as not spam.
 * @var bool|int  $open_modal      Whether the modal opens when the page loads.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$modal_attrs = array(
	'id'              => 'frm-not-spam-modal',
	'class'           => 'frm_hidden frm-modal frm_common_modal',
	'role'            => 'dialog',
	'aria-labelledby' => 'frm-not-spam-modal-title',
);

$submit_url = add_query_arg(
	'frm_action',
	'not_spam',
	FrmSpamEntriesController::get_tab_url( FrmSpamEntriesController::is_spam_tab(), FrmSpamEntriesController::get_list_form_id( $entry ) )
);

if ( $open_modal ) {
	$modal_attrs['data-open'] = '1';
}
?>
<div class="wrap frm-with-margin">
	<div class="frm_warning_style frm-spam-entry-notice" role="status">
		<p>
			<?php
			if ( $manual_spam ) {
				esc_html_e( 'This entry was manually marked as spam. It is hidden from views and other entry lists. Form actions that already ran were not reversed.', 'formidable' );
			} elseif ( $source_label ) {
				printf(
					/* translators: %s: Why the entry was marked as spam, like Akismet spam. */
					esc_html__( 'This entry was marked as spam, so form actions didn\'t run and it is hidden from views and other entry lists. Reason: %s.', 'formidable' ),
					esc_html( $source_label )
				);
			} else {
				esc_html_e( 'This entry was marked as spam, so form actions didn\'t run and it is hidden from views and other entry lists.', 'formidable' );
			}
			?>
		</p>
		<?php if ( $can_moderate ) { ?>
			<a href="#" class="button button-secondary frm-button-secondary frm-open-not-spam-modal">
				<?php esc_html_e( 'Mark as not spam', 'formidable' ); ?>
			</a>
		<?php } ?>
	</div>
</div>

<?php
if ( ! $can_moderate ) {
	return;
}
?>
<div <?php FrmAppHelper::array_to_html_params( $modal_attrs, true ); ?>>
	<div class="postbox">
		<div class="frm_modal_top">
			<div class="frm-modal-title" id="frm-not-spam-modal-title">
				<?php esc_html_e( 'Mark as not spam', 'formidable' ); ?>
			</div>
			<div>
				<a href="#" class="dismiss" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
				</a>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( $submit_url ); ?>">
			<input type="hidden" name="id" value="<?php echo absint( $entry->id ); ?>" />
			<?php wp_nonce_field( 'frm_not_spam', 'frm_not_spam_nonce' ); ?>

			<div class="frm_modal_content">
				<div class="inside">
					<p>
						<?php esc_html_e( 'This entry will move to the Entries tab, and it will be included in views and other entry lists.', 'formidable' ); ?>
					</p>

					<?php if ( $pending_actions ) { ?>
						<fieldset>
							<legend>
								<?php esc_html_e( 'Run these form actions now?', 'formidable' ); ?>
							</legend>
							<?php foreach ( $pending_actions as $pending_action ) { ?>
								<p>
									<label>
										<input type="checkbox" name="frm_not_spam_actions[]" value="<?php echo absint( $pending_action->ID ); ?>" />
										<?php echo esc_html( $pending_action->post_title ); ?>
									</label>
								</p>
							<?php } ?>
							<p class="howto">
								<?php esc_html_e( 'Actions with conditional logic only run when their conditions are met.', 'formidable' ); ?>
							</p>
						</fieldset>
					<?php } ?>

					<?php if ( $manual_spam ) { ?>
						<p class="howto">
							<?php esc_html_e( 'Form actions already ran when this entry was submitted, so they will not run again.', 'formidable' ); ?>
						</p>
					<?php } elseif ( ! $pending_actions ) { ?>
						<p class="howto">
							<?php esc_html_e( 'This form has no actions waiting to run.', 'formidable' ); ?>
						</p>
					<?php } ?>
				</div>
			</div>

			<div class="frm_modal_footer">
				<a href="#" class="button button-secondary frm-button-secondary dismiss">
					<?php esc_html_e( 'Cancel', 'formidable' ); ?>
				</a>
				<button type="submit" class="button button-primary frm-button-primary">
					<?php esc_html_e( 'Mark as not spam', 'formidable' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
