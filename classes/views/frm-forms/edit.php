<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_builder_page" class="frm_wrap">
	<div class="frm_page_container">
	<div class="frm_small_device_message_container">
		<div class="frm_small_device_message">
			<svg width="32" height="49" viewBox="0 0 32 49" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect x="1" y="1" width="30" height="47" rx="3" fill="#F5FAFF" stroke="#4199FD" stroke-width="2"/>
				<rect x="12" y="5" width="8" height="2" rx="1" fill="#C0DDFE"/>
				<path d="M23 33C23 29.6863 20.0899 27 16.5 27C12.9101 27 10 29.6863 10 33" stroke="#80BBFE" stroke-width="1.5"/>
				<circle cx="10.5" cy="20.5" r="1" fill="#80BBFE" stroke="#80BBFE"/>
				<circle cx="21.5" cy="20.5" r="1" fill="#80BBFE" stroke="#80BBFE"/>
			</svg>
			<b><?php esc_html_e( 'More on bigger devices', 'formidable' ); ?></b>
			<p><?php esc_html_e( 'For the best experience, we recommend using Formidable Forms on larger devices such as a desktop or tablet.', 'formidable' ); ?></p>
			<a href="#" class="frm-button-primary"><?php esc_html_e( 'Go back', 'formidable' ); ?></a>
		</div>
	</div>
	<?php
	require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_insert_fields.php';
	FrmAppHelper::get_admin_header(
		array(
			'label'      => __( 'Build Form', 'formidable' ),
			'form'       => $form,
			'hide_title' => true,
			'publish'    => array( 'FrmFormsController::form_publish_button', compact( 'values' ) ),
		)
	);
	?>

	<div class="columns-2">
	<?php require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field_links.php'; ?>
	<div id="post-body-content">

	<div class="frm_form_builder with_frm_style">

		<p class="frm_hidden frm-no-margin">
			<button class="frm_submit_<?php echo ! empty( $values['ajax_load'] ) ? '' : 'no_'; ?>ajax button-primary">
				<?php esc_attr_e( 'Update', 'formidable' ); ?>"
			</button>
		</p>

		<form method="post">
			<?php require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/form.php'; ?>
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
		<use xlink:href="#frm_settings_icon"></use>
	</svg>
	<svg id="frm_trash_svg" class="frmsvg">
		<use xlink:href="#frm_delete_icon"></use>
	</svg>
</div>
