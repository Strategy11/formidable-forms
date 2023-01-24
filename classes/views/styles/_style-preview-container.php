<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler. This view renders a container for both the target form and the sample form previews.
// It also includes the toggle to switch between the two views as only a single one is displayed at a time.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&form=782

?>
<div id="frm_style_preview">
	<div class="frm_m_12">
		<?php
		include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; // If a $message, $warnings, or $notes variable are not empty, it will be rendered here.
		?>
		<div id="frm_active_style_form">
			<?php
			// The right side body shows a preview (of the target form) so you can see the form you're actually styling.
			echo $target_form_preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
		<?php // Add a sample form to toggle between. This is toggled by the #frm_toggle_sample_form below this and is hidden by default. ?>
		<div id="frm_sample_form" class="frm_hidden">
			<?php
			$frm_settings = FrmAppHelper::get_settings();
			include $style_views_path . '_sample_form.php';
			?>
		</div>
		<?php if ( 'edit' !== $view ) { ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=edit&form=' . $form->id ) ); ?>" id="frm_edit_style" class="frm_floating_style_button" tabindex="0" role="button">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_pencil_icon', array( 'echo' => true ) ); ?> <?php esc_html_e( 'Edit style', 'formidable' ); ?>
			</a>
		<?php } ?>
		<button id="frm_toggle_sample_form" class="frm_floating_style_button">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_sample_form_icon', array( 'echo' => true ) ); ?> <span><?php esc_html_e( 'View sample form', 'formidable' ); ?></span>
		</button>
		<?php
		/**
		 * This is used in Pro to add the spinner to the preview (for previewing templates which have a delay).
		 *
		 * @since x.x
		 */
		do_action( 'frm_style_preview_after_toggle', $view );
		?>
	</div>
</div>
