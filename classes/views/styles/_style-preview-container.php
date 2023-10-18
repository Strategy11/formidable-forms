<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler. This view renders a container for both the target form and the sample form previews.
// It also includes the toggle to switch between the two views as only a single one is displayed at a time.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&form=782

$sample_form_is_on          = 1 === FrmAppHelper::simple_get( 'sample', 'absint' );
$sample_toggle_text         = $sample_form_is_on ? __( 'View my form', 'formidable' ) : __( 'View sample form', 'formidable' );
$active_form_wrapper_params = array(
	'id' => 'frm_active_style_form',
);
if ( $sample_form_is_on ) {
	$active_form_wrapper_params['class'] = 'frm_hidden';
}
?>
<div id="frm_style_preview">
	<div class="frm-m-12 frm-mt-0">
		<?php
		// If a $message, $warnings, or $notes variable are not empty, it will be rendered here.
		include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';
		?>
		<?php FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' ); // If Pro is not active, this will show an upsell. ?>
		<div id="frm_broken_styles_warning" class="frm_warning_style">
			<?php esc_html_e( 'One or more of your style settings may contain invalid characters that break form styling.', 'formidable' ); ?>
		</div>
		<div <?php FrmAppHelper::array_to_html_params( $active_form_wrapper_params, true ); ?>>
			<?php
			// The right side body shows a preview (of the target form) so you can see the form you're actually styling.
			echo $target_form_preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
		<?php // Add a sample form to toggle between. This is toggled by the #frm_toggle_sample_form below this and is hidden by default. ?>
		<div id="frm_sample_form">
			<?php
			$frm_settings = FrmAppHelper::get_settings();
			include $style_views_path . '_sample_form.php';
			unset( $frm_settings );
			?>
		</div>
		<?php if ( 'edit' !== $view ) { ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=edit&form=' . $form->id ) ); ?>" id="frm_edit_style" class="frm_floating_style_button button frm-button-secondary frm-with-icon" tabindex="0" role="button">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_pencil_icon', array( 'echo' => true ) ); ?> <?php esc_html_e( 'Edit style', 'formidable' ); ?>
			</a>
		<?php } ?>
		<button id="frm_toggle_sample_form" class="frm_floating_style_button button frm-button-secondary frm-with-icon">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_sample_form_icon', array( 'echo' => true ) ); ?> <span><?php echo esc_html( $sample_toggle_text ); ?></span>
		</button>
		<?php
		/**
		 * This is used in Pro to add a "Apply style" button to the preview.
		 *
		 * @since 6.0
		 *
		 * @param string $view 'edit' or 'list'.
		 */
		do_action( 'frm_style_preview_after_toggle', $view );
		?>
		<div id="frm_loading_style_placeholder">
			<span class="frm-wait frm_visible_spinner"></span>
			<strong><?php esc_html_e( 'Please wait', 'formidable' ); ?></strong>
			<p><?php esc_html_e( 'Updating CSS...', 'formidable' ); ?></p>
		</div>
	</div>
</div>
