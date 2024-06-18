<?php
/**
 * Form Templates - Name your form modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-name-your-form-modal" class="frm_wrap frm-form-templates-modal-item">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<h2><?php esc_html_e( 'Name your form', 'formidable' ); ?></h2>
		</div>
	</div>

	<div class="inside frm_grid_container frm-fields frm-px-md frm-mt-xs frm-m-0 frm-py-0">
		<p><?php esc_html_e( 'Before we save this form, do you want to name it first?', 'formidable' ); ?></p>

		<div class="frm_form_field">
			<label for="frm_new_form_name_input">
				<?php esc_html_e( 'Form Name (Optional)', 'formidable' ); ?>
			</label>
			<input type="text" name="frm_new_form_name_input" id="frm_new_form_name_input" placeholder="<?php esc_attr_e( 'Enter your form name', 'formidable' ); ?>" />
		</div>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-end frm-pt-sm frm-pb-md">
		<a href="#" id="frm-cancel-rename-form-button" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Cancel', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-save-form-name-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Save', 'formidable' ); ?>
		</a>
	</div>
</div>
