<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @var bool $should_display_videos
 */
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
					'new_link' => admin_url( 'admin.php?page=formidable-form-templates' ),
				),
			),
		)
	);
	?>
	<div class="frm-dashboard-container wrap frm-flex-box frm-justify-between">
		<?php $dashboard_view->get_welcome_banner(); ?>
		<div class="frm-flex-full frm-flex-box frm-flex-col">
			<?php $dashboard_view->get_counters(); ?>
			<div class="frm-dashboard-widget frm-card-item frm-px-0">
				<?php $dashboard_view->get_main_widget(); ?>
			</div>
			<?php $dashboard_view->get_payments(); ?>
			<?php $dashboard_view->get_bottom_widget(); ?>
		</div>
		<div class="frm-flex-box frm-flex-col">
			<div class="frm-dashboard-widget frm-card-item frm-license-widget">
				<?php $dashboard_view->get_license_management(); ?>
			</div>
			<?php
			if ( $should_display_videos ) {
				$dashboard_view->get_youtube_video( 'frm-dashboard-widget frm-card-item frm-yt-widget' );
			}
			?>
			<div class="frm-dashboard-widget frm-card-item frm-inbox-widget frm-px-0">
				<?php $dashboard_view->get_inbox(); ?>
			</div>
		</div>
	</div>
</div>
