<?php
/**
 * Add-Ons Page.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-addons-page" class="frm_wrap">
	<div class="frm_page_container">
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label' => __( 'Formidable Add-Ons', 'formidable' ),
			)
		);
		?>

		<div class="columns-2">
			<div id="frm-addons-sidebar" class="frm-right-panel frm-flex-col frm-hide-js">
				<div class="frm-scrollbar-wrapper frm-flex-col frm-gap-sm">
					<?php
					// Search box.
					FrmAppHelper::show_search_box(
						array(
							'input_id'    => 'addon',
							'placeholder' => __( 'Search Add-Ons', 'formidable' ),
							'tosearch'    => 'frm-searchable-addon',
						)
					);

					// Categories list.
					// require $view_path . 'categories.php';
					?>
				</div>
			</div>

			<div id="post-body-content" class="frm-flex-col frm-gap-sm frm-p-md frm-hide-js">
				<?php
				// TODO: Clarify this old code.
				// require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';

				// Add-Ons list.
				require $view_path . 'list.php';
				?>
			</div>
		</div>
	</div>
</div>
