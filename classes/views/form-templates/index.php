<?php
/**
 * Form Templates Page.
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
<div id="frm-form-templates-page" class="frm_wrap">
	<div class="frm_page_container">
		<!-- Page Header -->
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'   => __( 'Form Templates', 'formidable' ),
				'publish' => array( 'FrmFormTemplatesController::get_header_cancel_button', array() ),
			)
		);
		?>

		<!-- Page Body -->
		<div class="columns-2">
			<!-- Page Sidebar -->
			<div class="frm-right-panel">
				<div class="frm-scrollbar-wrapper">
					<?php
					// Search box.
					FrmAppHelper::show_search_box(
						array(
							'input_id'    => 'template',
							'placeholder' => __( 'Search Templates', 'formidable' ),
							'tosearch'    => 'frm-searchable-template',
						)
					);

					// Categories list.
					require $view_path . 'categories.php';
					?>
				</div>
			</div><!-- .frm-right-panel -->

			<!-- Page Content -->
			<div id="post-body-content" class="frm_hidden">
				<!-- Create a blank form -->
				<span id="frm-form-templates-create-form">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon', array( 'aria-label' => _x( 'Create', 'form templates: create a blank form', 'formidable' ) ) ); ?>
					<span><?php esc_html_e( 'Create a blank form', 'formidable' ); ?></span>
				</span><!-- #frm-form-templates-new-form -->
				<span id="frm-form-templates-create-form-divider" class="frm-form-templates-divider"></span>

				<?php
				// Templates list.
				require $view_path . 'templates.php';
				?>
			</div><!-- #post-body-content -->
		</div><!-- .columns-2 -->
	</div><!-- .frm_page_container -->
</div><!-- #frm-from-templates-page.frm_wrap -->

<div class="frm_hidden">
	<!-- Create new form -->
	<div id="frm-details-block">
		<form name="frm-new-template" id="frm-new-template" method="post" class="field-group">
			<p>
				<label for="frm_template_name" id="frm_new_name" data-template="<?php esc_attr_e( 'Template Name', 'formidable' ); ?>" data-form="<?php esc_html_e( 'Form Name', 'formidable' ); ?>">
				<?php esc_html_e( 'Form Name', 'formidable' ); ?>
				</label>
				<input type="text" name="template_name" id="frm_template_name" class="frm_long_input" />
			</p>

			<input type="hidden" name="template_desc" id="frm_template_desc" />
			<input type="hidden" name="link" id="frm_link" value="" />
			<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />

			<button type="submit" class="button-primary frm-button-primary">
				<?php esc_html_e( 'Create', 'formidable' ); ?>
			</button>
		</form>
	</div>
</div>
