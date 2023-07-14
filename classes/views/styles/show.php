<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the main view file used in the visual styler. It is used for both the "list" and "edit" view types.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&frm_action=edit&form=782

?>
<div class="frm_page_container">
	<?php // The embed button expects that the form ID is available as a #form_id field. ?>
	<input type="hidden" id="form_id" value="<?php echo absint( $form->id ); ?>" />

	<?php
	// Wrap the header in a .frm_wrap element so the h1 tag gets styled properly.
	// We want to avoid putting .frm_wrap on the whole page container to avoid back end styling in the visual styler preview.
	?>
	<div class="frm_wrap">
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
					),
				),
			)
		);
		?>
	</div>
	<div id="frm_styler_wrapper" class="columns-2">
		<?php
		$view_file = 'list' === $view ? 'list' : 'edit';
		include $style_views_path . '_styles-' . $view_file . '.php'; // Render view based on type (either _styles-list.php or _styles-edit.php).

		include $style_views_path . '_style-preview-container.php'; // Render preview container.
		?>
	</div>
</div>
