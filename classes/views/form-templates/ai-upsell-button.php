<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x
 */
?>
<button id="frm-form-templates-create-ai-form" class="frm-flex-box frm-items-center frm-form-templates-create-button frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Create with AI options', 'formidable' ); ?>">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm-ai-form-icon', array( 'aria-label' => _x( 'Create', 'form templates: create an AI generated form', 'formidable' ) ) ); ?>
	<span><?php esc_html_e( 'Create with AI', 'formidable' ); ?></span>
	<?php FrmAppHelper::show_pill_text( __( 'BETA', 'formidable' ) ); ?>
</button>
