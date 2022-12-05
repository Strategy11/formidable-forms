<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&frm_action=edit&form=782

?>
<div class="frm_wrap">
	<div class="frm_page_container">
		<input type="hidden" id="form_id" value="<?php echo absint( $form->id ); ?>" /><?php // The embed button expects that the form ID is available as a #form_id field. ?>

		<?php
		FrmAppHelper::get_admin_header(
			array(
				'form'       => $form,
				'hide_title' => true,
				'publish'    => array(
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
						<?php
						$view_file = 'list' === $view ? 'list' : 'edit';
						include $style_views_path . '_styles-' . $view_file . '.php';

						include $style_views_path . '_style-preview-container.php'; // Render preview container.
						?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
</div>
