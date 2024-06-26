<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';

FrmAddonsHelper::show_pro_inactive_cta();
FrmAddonsHelper::show_upgrade_renew_cta();
FrmAddonsHelper::get_reconnect_link();
?>

<ul id="frm-addons-list" class="frm-page-skeleton-grid-layout frm-mb-xs">
	<?php
	foreach ( $addons as $slug => $addon ) {
		require $view_path . 'addon.php';
	}
	?>
</ul>
