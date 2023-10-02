<?php
/**
 * Form Templates - Modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
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
			</a><!-- .dismiss -->

			<?php
			foreach ( $view_parts as $modal => $file ) {
				require $view_path . $file;
			}
			?>
		</div><!-- .postbox -->
	</div><!-- .metabox-holder -->
</div>
