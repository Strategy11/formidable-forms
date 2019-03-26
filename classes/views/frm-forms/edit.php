<div id="frm_builder_page" class="frm_wrap">
	<div class="frm_page_container">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Build Form', 'formidable' ),
			'form'        => $form,
			'hide_title'  => true,
			'close'       => '?page=formidable',
			'publish'     => array( 'FrmFormsController::form_publish_button', compact( 'values' ) ),
		)
	);
	?>

	<div class="columns-2">
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field_links.php' ); ?>
	<div id="post-body-content">

	<div class="frm_form_builder with_frm_style">

		<?php
		// Add form messages.
		require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
		?>

		<p class="frm_hidden frm-no-margin">
			<input type="button" value="<?php esc_attr_e( 'Update', 'formidable' ); ?>" class="frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary" />
			<span class="frm-loading-img"></span>
		</p>

		<form method="post">
			<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/form.php' ); ?>
		</form>

	</div>
	</div>

	</div>
	</div>
</div>
