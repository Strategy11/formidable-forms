<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_hidden">
	<?php
	FrmAppHelper::icon_by_class( 'frmfont frm_clone_icon' );
	FrmAppHelper::icon_by_class( 'frmfont frm_file_icon' );
	?>
	<svg id="frm_copy_embed_form_icon" class="frmsvg">
		<use xlink:href="#frm_clone_icon"></use>
	</svg>
	<svg id="frm_select_existing_page_icon" class="frmsvg">
		<use xlink:href="#frm_file_icon"></use>
	</svg>
	<svg id="frm_create_new_page_icon" class="frmsvg">
		<use xlink:href="#frm_plus_icon"></use>
	</svg>
	<svg id="frm_insert_manually_icon" class="frmsvg">
		<use xlink:href="#frm_code_icon"></use>
	</svg>
</div>
