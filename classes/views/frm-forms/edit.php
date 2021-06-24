<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_builder_page" class="frm_wrap">
	<div class="frm_page_container">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Build Form', 'formidable' ),
			'form'        => $form,
			'hide_title'  => true,
			'publish'     => array( 'FrmFormsController::form_publish_button', compact( 'values' ) ),
		)
	);
	?>

	<div class="columns-2">
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field_links.php' ); ?>
	<div id="post-body-content">

	<div class="frm_form_builder with_frm_style">

		<p class="frm_hidden frm-no-margin">
			<button class="frm_submit_<?php echo ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary">
				<?php esc_attr_e( 'Update', 'formidable' ); ?>"
			</button>
		</p>

		<form method="post">
			<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/form.php' ); ?>
		</form>

	</div>
	</div>

	</div>
	</div>
</div>
<div class="frm_hidden">
	<svg id="frm_break_field_group_svg" class="frmsvg">
		<use xlink:href="#frm_break_field_group_icon"></use>
	</svg>
	<svg id="frm_gear_svg" class="frmsvg">
		<use xlink:href="#frm_gear_icon"></use>
	</svg>
	<svg id="frm_trash_svg" class="frmsvg">
		<use xlink:href="#frm_trash_icon"></use>
	</svg>
</div>
