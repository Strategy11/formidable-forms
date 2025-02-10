<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-dismissable-cta" class="frm-modal settings-lite-cta" style="margin-top: 40px;">
	<div class="postbox" style="border:none;">
		<div class="inside">
			<a href="#" class="dismiss" style="position:absolute;z-index:999;right: calc(var(--gap-md) + 8px);top:calc(var(--gap-md) + 12px);" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>

			<div class="cta-inside">
				<?php
				if ( ! FrmAppHelper::pro_is_installed() ) {
					$dashboard_helper = FrmDashboardController::get_dashboard_helper();
					$dashboard_helper->get_bottom_widget();
				}
				?>
			</div>
		</div>
	</div>
</div>
