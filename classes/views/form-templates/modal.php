<?php
/**
 * Form Templates - Modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
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
