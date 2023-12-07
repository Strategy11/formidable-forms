<?php
/**
 * Form Templates - Create new template modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-create-template-modal" class="frm_wrap frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<h2><?php esc_html_e( 'Create New Template', 'formidable' ); ?></h2>
		</div>
	</div>

	<div class="inside frm_grid_container frm-fields frm-px-md frm-py-0 frm-mt-sm frm-m-0">
		<div class="frm_form_field">
			<label for="frm-create-template-modal-forms-select">
				<?php esc_html_e( 'Select form for a new template', 'formidable' ); ?>
			</label>

			<select name="frm-create-template-modal-forms-select" id="frm-create-template-modal-forms-select">
				<option value=""> </option>

				<?php if ( empty( $published_forms ) ) { ?>
					<option value="no-forms" disabled>
						<?php esc_html_e( 'You have not created any forms yet.', 'formidable' ); ?>
					</option>
					<?php
				}

				foreach ( $published_forms as $form ) {
					$form_description = FrmAppHelper::kses( $form->description, array( 'a', 'i', 'span', 'use', 'svg' ) );
					?>
					<option value="<?php echo esc_attr( $form->id ); ?>" data-name="<?php echo esc_attr( $form->name ); ?>" data-description="<?php echo esc_attr( $form_description ); ?>">
						<?php echo esc_html( FrmFormsHelper::edit_form_link_label( $form ) ); ?>
					</option>
				<?php } ?>
			</select>
		</div>

		<div class="frm_form_field">
			<label for="frm_create_template_name">
				<?php esc_html_e( 'Template name', 'formidable' ); ?>
			</label>
			<input type="text" name="frm_create_template_name" id="frm_create_template_name" disabled />
		</div>

		<div class="frm_form_field">
			<label for="frm_create_template_description">
				<?php esc_html_e( 'Description', 'formidable' ); ?>
			</label>
			<textarea name="frm_create_template_description" id="frm_create_template_description" class="frm-rounded-sm" disabled></textarea>
		</div>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-between frm-pt-sm frm-pb-md">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Cancel', 'formidable' ); ?>
		</a>
		<a id="frm-create-template-button" href="#" class="button button-primary frm-button-primary disabled" role="button">
			<?php esc_html_e( 'Create Template', 'formidable' ); ?>
		</a>
	</div>
</div>
