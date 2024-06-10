<?php
/**
 * Add-Ons list.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmAddonsHelper::show_upgrade_renew_cta( compact( 'expired', 'upgrade_link', 'renew_link' ) );
FrmAddonsHelper::get_reconnect_link();
?>
<ul id="frm-addons-list" class="frm-addons-list frm-addons-grid-layout frm-mb-xs">
	<?php
	foreach ( $addons as $slug => $addon ) {
		require $view_path . 'addon.php';
	}
	?>
</ul>
