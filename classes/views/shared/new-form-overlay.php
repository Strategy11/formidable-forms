<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_new_form_modal" class="frm_hidden">
	<div class="metabox-holder">
		<div class="postbox">
			<div>
				<div>
					<?php esc_html_e( 'Create new form', 'formidable' ); ?>
				</div>
				<div>
					<a href="#" class="dismiss">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
				</div>
			</div>
			<div class="inside">
				<div class="cta-inside frmcenter">
					<?php FrmFormsController::list_templates_new(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="frm_hidden">
	<?php
	FrmAppHelper::icon_by_class( 'frmfont frm_eye_simple' );
	FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' );
	?>
	<div id="hover-icons-template" class="hover-icons">
		<a href="#" class="preview-form" title="<?php esc_html_e( 'Preview form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_eye_simple"></use>
			</svg>
		</a><a href="#" class="create-form" title="<?php esc_html_e( 'Create form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_plus_icon"></use>
			</svg>
		</a>
	</div>
</div>
