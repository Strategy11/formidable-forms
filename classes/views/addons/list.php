<?php
/**
 * Add-Ons list.
 *
 * @package Formidable
 *
 * @var array<string, array> $addons            Available add-ons keyed by slug.
 * @var string               $view_path         Absolute path to the views/addons/ directory, with trailing slash.
 * @var string               $request_addon_url URL for requesting a new add-on.
 * @var string               $license_type      Current license type or empty string.
 * @var string               $pricing           Upgrade URL used for CTAs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmAddonsHelper::show_upgrade_renew_cta();
FrmAddonsHelper::get_reconnect_link();
?>

<ul id="frm-addons-list" class="frm-list-grid-layout frm-mb-xs">
	<?php
	foreach ( $addons as $slug => $addon ) {
		require $view_path . 'addon.php';
	}
	?>
</ul>

<div class="frm-addons-request-addon frm-py-2xs frm-mt-xs">
	<span><?php esc_html_e( 'Not finding what you need?', 'formidable' ); ?></span>
	<a class="frm-font-semibold" href="<?php echo esc_url( $request_addon_url ); ?>" target="_blank"><?php esc_html_e( 'Request Add-On', 'formidable' ); ?></a>
</div>
