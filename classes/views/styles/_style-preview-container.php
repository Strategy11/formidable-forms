<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler. This view renders a container for both the target form and the sample form previews.
// It also includes the toggle to switch between the two views as only a single one is displayed at a time.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&form=782

?>
<div id="frm_style_preview">
	<?php
	// Get form HTML before displaying warnings and notes so we can check global data without adding extra database calls.
	$target_form_preview_html = FrmStylesHelper::get_html_for_form_preview( $form->id );

	$warnings = FrmStylesHelper::get_warnings_for_styler_preview( $style, $default_style, $view );
	$notes    = is_callable( 'FrmProStylesController::get_notes_for_styler_preview' ) ? FrmProStylesController::get_notes_for_styler_preview() : array();
	include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; // If a $message or $warnings variable are not empty, it will be rendered here.

	FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' ); // If Pro is not active, this will show an upsell.
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
		<a href="<?php echo esc_url( admin_url( 'themes.php?page=formidable-styles&frm_action=edit&form=' . $form->id ) ); ?>" id="frm_edit_style" class="frm_floating_style_button" tabindex="0" role="button">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_pencil_icon', array( 'echo' => true ) ); ?> <?php esc_html_e( 'Edit style', 'formidable' ); ?>
		</a>
	<?php } ?>
	<button id="frm_toggle_sample_form" class="frm_floating_style_button">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_sample_form_icon', array( 'echo' => true ) ); ?> <span><?php esc_html_e( 'View sample form', 'formidable' ); ?></span>
	</button>
</div>
