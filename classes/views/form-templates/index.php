<?php
/**
 * Form Templates Page.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-form-templates-page" class="frm_wrap">
	<div class="frm_page_container">
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'   => __( 'Form Templates', 'formidable' ),
				'publish' => array( 'FrmFormTemplatesController::get_header_cancel_button', array() ),
			)
		);
		?>

		<div class="columns-2">
			<div id="frm-form-templates-sidebar" class="frm-right-panel frm-flex-col frm-hide-js">
				<div class="frm-scrollbar-wrapper frm-flex-col frm-gap-sm">
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
			</div>

			<div id="post-body-content" class="frm-flex-col frm-gap-sm frm-p-md frm-hide-js">
				<div class="frm-form-templates-grid-layout">
					<button id="frm-form-templates-create-form" class="frm-flex-box frm-items-center">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon', array( 'aria-label' => _x( 'Create', 'form templates: create a blank form', 'formidable' ) ) ); ?>
						<span><?php esc_html_e( 'Create a blank form', 'formidable' ); ?></span>
					</button>
				</div>
				<span id="frm-form-templates-create-form-divider" class="frm-form-templates-divider frm-mt-xs frm-mb-xs"></span>

				<?php
				// Templates list.
				require $view_path . 'list.php';
				?>
			</div>
		</div>
	</div>

	<?php
	/**
	 * Hidden form used for creating or using a form template.
	 *
	 * This form works in the background and is not shown to the user. It gets submitted by JavaScript
	 * when the user clicks on either the 'Create a blank form' or 'Use template' buttons, sending the user's choices.
	 *
	 * @see formidable_admin::installNewForm() This method handles the form submission.
	 */
	?>
	<form class="frm_hidden" id="frm-new-template" name="frm-new-template" method="post">
		<input type="hidden" name="template_name" id="frm_template_name" value="" />
		<input type="hidden" name="template_desc" id="frm_template_desc" />
		<input type="hidden" name="link" id="frm_link" value="" />
		<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />
	</form>
</div>
