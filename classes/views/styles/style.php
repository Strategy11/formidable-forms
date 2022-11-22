<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable&frm_action=style&id=782

?>
<div class="frm_wrap">
	<div class="frm_page_container">
		<input type="hidden" id="form_id" value="<?php echo absint( $form->id ); ?>" /><?php // The embed button expects that the form ID is available as a #form_id field. ?>
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'form'        => $form,
				'hide_title'  => true,
				'publish' => array(
					'FrmFormsController::form_publish_button',
					array(
						'values' => array(
							'form_key' => $form->form_key, // Pass this so that the Preview dropdown works.
						),
					)
				),
			)
		);
		?>
		<div class="frm_form_fields frm_sample_form frm_forms frm_pro_form">
			<fieldset>
				<div class="frm_fields_container">
					<div id="frm_style_page_wrapper">
						<div id="frm_style_sidebar" class="frm_grid_container frm5">
							<?php
							/**
							 * Pro needs to hook in here to add the "New Style" trigger.
							 *
							 * @since x.x
							 *
							 * @param array $args {
							 *     @type stdClass $form
							 * }
							 */
							do_action( 'frm_style_sidebar_top', compact( 'form' ) );
							?>

							<form id="frm_style_form" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=style&id=' . $form->id .'&t=advanced_settings' ) ); ?>">
								<input type="hidden" name="style_id" value="<?php echo absint( $active_style->ID ); ?>" />
								<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
								<?php wp_nonce_field( 'frm_save_form_style_nonce', 'frm_save_form_style' ); ?>
							</form>
							<?php
							// TODO the design has a "New style" option here.
							// TODO this will trigger a new modal.

							array_walk(
								$styles,
								function( $style ) use ( $style_views_path, $active_style, $default_style ) {
									FrmStylesHelper::echo_style_card( $style, $style_views_path, $active_style, $default_style );
								}
							);
							?>
						</div>
						<?php // Preview area. ?>
						<div id="frm_style_preview" class="frm7">
							<div id="frm_active_style_form">
								<?php
								// The right side body shows a preview (of the target form) so you can see the form you're actually styling.
								add_filter( 'frm_is_admin', '__return_false' ); // Force is_admin to false so the "Entry Key" field doesn't render in the preview.
								echo FrmFormsController::show_form(  $form->id, '', 'auto', 'auto' );
								?>
							</div>
							<?php // Add a sample form to toggle between. This is toggled by the #frm_toggle_sample_form below this. ?>
							<div id="frm_sample_form" class="frm_hidden">
								<?php 
								$style = $default_style;
								$frm_settings = $frm_settings = FrmAppHelper::get_settings();
								include $style_views_path . '_sample_form.php';
								?>
							</div>
							<?php // TODO: Hide this button if it is not the active style. ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=edit&id=' . $active_style->ID ) ); ?>" id="frm_edit_style" class="frm_floating_style_button" tabindex="0" role="button">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_pencil_icon', array( 'echo' => true ) ); ?> <?php esc_html_e( 'Edit style', 'formidable' ); ?>
							</a>
							<button id="frm_toggle_sample_form" class="frm_floating_style_button">
								<?php FrmAppHelper::icon_by_class( 'frmfont frm_sample_form_icon', array( 'echo' => true ) ); ?> <?php esc_html_e( 'View sample form', 'formidable' ); ?>
							</button>
						</div><?php // End #frm_style_sidebar ?>
					</div><?php // End #frm_style_page_wrapper ?>
				</div><?php // End .frm_fields_container ?>
			</fieldset>
		</div><?php // End .frm_form_fields ?>
	</div><?php // End .frm_page_container ?>
</div>
