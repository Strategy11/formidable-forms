<?php
/**
 * Add-Ons list.
 *
 * @package Formidable
 *
 * @var array<string, array> $addons            Available add-ons keyed by slug.
 * @var string               $view_path         Absolute path to the views/addons/ directory, with trailing slash.
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
