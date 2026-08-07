<?php
/**
 * Add-Ons Page.
 *
 * @package Formidable
 *
 * @var string               $view_path         Absolute path to the views/addons/ directory, with trailing slash.
 * @var array                $installed_addons  Installed add-on plugins keyed by slug.
 * @var array<string, array> $addons            Available add-ons keyed by slug.
 * @var array                $errors            API errors, if any.
 * @var string               $license_type      Current license type or empty string.
 * @var string               $request_addon_url URL for requesting a new add-on.
 * @var array                $pro               Pro add-on entry prepended to $addons.
 * @var string               $pricing           Upgrade URL used for CTAs.
 * @var array<string, array> $categories        Add-on categories keyed by slug, each with 'name' and 'count'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-addons-page" class="frm_wrap frm-page-skeleton">
	<div class="frm_page_container">
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label' => __( 'Formidable Add-Ons', 'formidable' ),
			)
		);
		?>

		<div class="columns-2">
			<div id="frm-page-skeleton-sidebar" class="frm-right-panel frm-flex-col frm-hide-js">
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
					require $view_path . 'categories.php';
					?>
				</div>
			</div>

			<div id="post-body-content" class="frm-flex-col frm-gap-sm frm-p-xl frm-hide-js">
				<?php require $view_path . 'list.php'; ?>
			</div>
		</div>
	</div>
</div>
