<?php
/**
 * Form Templates - Modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Return early if no view parts to render.
if ( ! $view_parts ) {
	return;
}
?>
<div id="frm-form-templates-modal" class="frm-modal frm_common_modal frm_hidden" frm-page="">
	<div class="metabox-holder">
		<div class="postbox">
			<a class="frm-modal-close dismiss" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
			</a>

			<?php
			foreach ( $view_parts as $modal => $file ) {
				require $view_path . $file;
			}
			?>
		</div>
	</div>
</div>
