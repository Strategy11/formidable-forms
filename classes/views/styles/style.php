<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable&frm_action=style&id=782

?>

<div class="frm_wrap">
	<div class="frm_page_container">
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'form'        => $form,
				'hide_title'  => true,
			)
		);
		?>
		<div class="frm_form_fields frm_sample_form frm_forms frm_pro_form">
			<fieldset>
				<div class="frm_fields_container">
					<div class="frm_grid_container">
						<div id="frm_style_sidebar" class="frm_grid_container frm5">
							<?php
							// TODO the design has a "New style" option here.
							// TODO this will trigger a new modal.

							array_walk(
								$styles,
								function( $style ) use ( $style_views_path, $active_style, $default_style ) {
									include $style_views_path . '_custom-style-card.php';
								}
							);
							?>
						</div>
						<?php // Preview area. ?>
						<div class="frm7">
							<div id="frm_active_style_form">
								<?php
								/**
								 * The right side body shows a preview (of the target form) so you can see the form you're actually styling.
								 * TODO: There is a floating button here that links to the Style editor page.
								 */
								add_filter( 'frm_is_admin', '__return_false' ); // Force is_admin to false so the "Entry Key" field doesn't render in the preview.
								echo FrmFormsController::show_form(  $form->id, '', 'auto', 'auto' );
								?>
							</div>
							<div id="frm_sample_form" class="frm_hidden">
								<?php 
								$style = $default_style;
								$frm_settings = $frm_settings = FrmAppHelper::get_settings();
								include $style_views_path . '_sample_form.php';
								?>
							</div>
							<?php // TODO: Hide this button if it is not the active style. ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=edit&id=' . $active_style->ID ) ); ?>" id="frm_edit_style" class="button-primary frm-button-primary">
								<?php esc_html_e( 'Edit style', 'formidable' ); ?>
							</a>
							<button id="frm_toggle_sample_form" class="button-primary frm-button-primary">
								<?php esc_html_e( 'View sample form', 'formidable' ); ?>
							</button>
						</div><?php // End #frm_style_sidebar ?>
					</div><?php // End .frm_grid_container ?>
				</div><?php // End .frm_fields_container ?>
			</fieldset>
		</div><?php // End .frm_form_fields ?>
	</div><?php // End .frm_page_container ?>
</div>
