<?php
/**
 * Form Templates - Name your form modal.
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
?>
<div id="frm-name-your-form-modal" class="frm-form-templates-modal-item">
	<!-- Modal Header -->
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span class="frm-modal-title-text"><?php esc_html_e( 'Name your form', 'formidable' ); ?></span>
		</div>
	</div><!-- .frm_modal_top -->

	<!-- Modal Body -->
	<div class="inside">
		<p><?php esc_html_e( 'Before we save your form, do you want to name your form first?', 'formidable' ); ?></p>
		<p class="frm-new-form-name-field">
			<label for="frm_new_form_name">
				<?php esc_html_e( 'Form Name (Optional)', 'formidable' ); ?>
			</label>
			<input type="text" name="frm_new_form_name" id="frm_new_form_name" placeholder="<?php esc_html_e( 'Enter your form name', 'formidable' ); ?>" class="frm_long_input" />
		</p>
	</div><!-- .inside -->

	<!-- Modal Footer -->
	<div class="frm_modal_footer">
		<a href="#" id="frm-cancel-rename-form-button" class="button button-secondary frm-button-secondary" role="button">
			<?php esc_html_e( 'Cancel', 'formidable' ); ?>
		</a>
		<a  href="#" id="frm-rename-form-button" class="button button-primary frm-button-primary" role="button" data-disabled="true">
			<?php esc_html_e( 'Rename', 'formidable' ); ?>
		</a>
	</div><!-- .frm_modal_footer -->
</div>