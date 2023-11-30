<?php
/**
 * Form Templates - Create new template modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-create-template-modal" class="frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<h2><?php esc_html_e( 'Create New Template', 'formidable' ); ?></h2>
		</div>
	</div>

	<div class="inside frm-flex-col frm-gap-sm frm-px-md frm-mt-md frm-mb-xs">
		<div class="frm-flex-col frm-gap-xs">
			<label for="frm-create-template-modal-forms-select" class="frm-m-0">
				<?php esc_html_e( 'Select form for a new template', 'formidable' ); ?>
			</label>

			<select name="frm-create-template-modal-forms-select" id="frm-create-template-modal-forms-select">
				<option value=""><?php esc_html_e( 'Select form for a new template', 'formidable' ); ?></option>

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
						<?php echo esc_html( empty( $form->name ) ? __( '(no title)', 'formidable' ) : $form->name ); ?>
					</option>
				<?php } ?>
			</select>
		</div>

		<div class="frm-flex-col frm-gap-xs">
			<label for="frm_create_template_name" class="frm-m-0">
				<?php esc_html_e( 'Template Name', 'formidable' ); ?>
			</label>
			<input type="text" name="frm_create_template_name" id="frm_create_template_name" disabled />
		</div>

		<div class="frm-flex-col frm-gap-xs">
			<label for="frm_create_template_description" class="frm-m-0">
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
