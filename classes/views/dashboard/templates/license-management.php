<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-dashboard-license-management">
	<h3>
		<?php esc_html_e( 'License Key', 'formidable' ); ?>
	</h3>
	<span>
		<?php echo esc_html( FrmAppHelper::copy_for_lite_license() ); ?>
	</span>
	<div class="frm-flex-box frm-gap-xs">
		<?php FrmDashboardHelper::show_connect_links(); ?>
	</div>
</div>
