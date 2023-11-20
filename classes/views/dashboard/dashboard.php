<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap frm-dashboard-wrapper">
	<?php
	FrmAppHelper::include_svg();
	FrmAppHelper::get_admin_header(
		array(
			'label'   => __( 'Dashboard', 'formidable' ),
			'publish' => array(
				'FrmAppHelper::add_new_item_link',
				array(
					'trigger_new_form_modal' => current_user_can( 'frm_edit_forms' ),
				),
			),
		)
	);
	?>
	<?php $dashboard_view->get_welcome_banner(); ?>
	<div class="frm-dashboard-container frm-flex-box frm-justify-between">
		<div>
			<?php $dashboard_view->get_counters(); ?>
			<div class="frm-dashboard-widget frm-animate">
				<?php $dashboard_view->get_main_widget(); ?>
			</div>
			<?php $dashboard_view->get_payments(); ?>
			<div class="frm-dashboard-widget frm-animate">
				<?php $dashboard_view->get_bottom_widget(); ?>
			</div>
		</div>
		<div>
			<div class="frm-dashboard-widget frm-license-widget frm-animate">
				<?php $dashboard_view->get_license_management(); ?>
			</div>
			<?php if ( '' !== $dashboard_view->get_youtube_video( false ) ) : ?>
				<div class="frm-dashboard-widget frm-yt-widget frm-animate">
					<?php $dashboard_view->get_youtube_video(); ?>
				</div>
			<?php endif; ?>
			<div class="frm-dashboard-widget frm-inbox-widget frm-animate">
				<?php $dashboard_view->get_inbox(); ?>
			</div>
		</div>
	</div>

	<div class="clear"></div>
</div>
