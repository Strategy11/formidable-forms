<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_builder_page" class="frm_wrap">
	<div class="frm_page_container">
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

	<div class="frm-builder-outline hide-if-no-js">
		<button type="button" id="frm-builder-outline-toggle" class="button frm-button-secondary frm-with-icon" aria-expanded="false" aria-controls="frm-builder-outline">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_map_icon frm-builder-outline-icon', array( 'aria-hidden' => 'true' ) ); ?>
			<span class="frm-builder-outline-current"><?php esc_html_e( 'Form outline', 'formidable' ); ?></span>
		</button>
	</div>
	<div id="frm-builder-outline" class="frm-builder-outline-popover hide-if-no-js" role="dialog" aria-label="<?php esc_attr_e( 'Form outline', 'formidable' ); ?>" hidden>
		<p class="frm-search">
			<label class="screen-reader-text" for="frm-builder-outline-search-input">
				<?php esc_html_e( 'Search pages and fields', 'formidable' ); ?>
			</label>
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon frm_svg20' ); ?>
			<input type="search" id="frm-builder-outline-search-input" class="frm-search-input" autocomplete="off" placeholder="<?php esc_attr_e( 'Search pages and fields', 'formidable' ); ?>" />
		</p>
		<nav aria-label="<?php esc_attr_e( 'Form outline', 'formidable' ); ?>">
			<ul class="frm-builder-outline-list"></ul>
		</nav>
		<div class="frm-builder-outline-empty frm_hidden">
			<strong class="frm-builder-outline-empty-title"></strong>
			<span class="frm-builder-outline-empty-text"></span>
		</div>
		<p class="screen-reader-text frm-builder-outline-status" aria-live="polite"></p>
	</div>
	</div>

	</div>
	</div>
</div>
<div class="frm_hidden">
	<svg id="frm_break_field_group_svg" class="frmsvg">
		<use href="#frm_break_field_group_icon"></use>
	</svg>
	<svg id="frm_gear_svg" class="frmsvg">
		<use href="#frm_settings_icon"></use>
	</svg>
	<svg id="frm_trash_svg" class="frmsvg">
		<use href="#frm_delete_icon"></use>
	</svg>
</div>
