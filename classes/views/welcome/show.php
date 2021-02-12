<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<div class="frm_grid_container frm_no_grid_750">
		<div class="frm6">
			<div class="frm-ws-block-1">
				<div class="frm-ws-logo">
					<?php
					FrmAppHelper::show_logo(
						array(
							'height' => 68.76,
							'width'  => 69,
						)
					);
					?>
				</div>
				<h1><?php esc_html_e( 'Welcome to Formidable Forms!', 'formidable' ); ?></h1>
				<p><?php esc_html_e( 'Thanks for choosing Formidable Froms - The most powerful and vesatile form builder for Wordpress', 'formidable' ); ?></p>
				<?php FrmWelcomeScreenController::maybe_show_license_box(); ?>

			</div>
		</div>
		<div class="frm6">
			<div class="frm-player">
				<iframe width="480" height="240" src="https://www.youtube.com/embed/-eGuL_OWHw4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<div class="frm_grid_container frm-top-spacing">
		<div class="frm6 frm-ws-block frm-create-blank-form">
			<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?></div>
			<div class="frm-fill">
				<h3><?php esc_html_e( 'New Blank Form', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Create a new view from scratch', 'formidable' ); ?></p>
			</div>
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_right_icon frm-right-arrow' ); ?>
		</div>
		<div class="frm6 frm-ws-block frm-trigger-new-form-modal">
			<div class="frm-icon frm-icon-briefcase"><?php FrmAppHelper::icon_by_class( 'frmfont frm_briefcase_icon' ); ?></div>
			<div class="frm-fill">
				<h3><?php esc_html_e( 'New Form From a Template', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Check out our powerful pre-built templates', 'formidable' ); ?></p>
			</div>
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_right_icon frm-right-arrow' ); ?>
		</div>
	</div>

	<div class="frm-ws-carousel-wrapper">
		<h4><?php esc_html_e( 'Get limitless possibilities', 'formidable' ); ?></h4>

		<div class="frm_grid_container">
			<div class="frm3">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_ws_calculator_icon' ); ?></div>
				<h3><?php esc_html_e( 'Calculators', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Give instant online estimates and calculate advanced product options.', 'formidable' ); ?></p>
				<?php FrmWelcomeScreenController::upgrade_to_pro_button(); ?>
			</div>
			<div class="frm3">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_chat_bubbles_icon' ); ?></div>
				<h3><?php esc_html_e( 'Survey and Polls', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Collect customer feedback & data like a pro - no coding required.', 'formidable' ); ?></p>
				<?php FrmWelcomeScreenController::upgrade_to_pro_button(); ?>
			</div>
			<div class="frm3">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_paperclip_icon' ); ?></div>
				<h3><?php esc_html_e( 'File uploads', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Easily upload documents, files, photos, and music for user avatars, featured images, or email attachments.', 'formidable' ); ?></p>
				<?php FrmWelcomeScreenController::upgrade_to_pro_button(); ?>
			</div>
			<div class="frm3">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_listings_icon' ); ?></div>
				<h3><?php esc_html_e( 'Display form data with views', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Now you can display form data in custom Views without any PHP.', 'formidable' ); ?></p>
				<?php FrmAddonsController::conditional_action_button( 'views', 'views-info' ); ?>
			</div>
		</div>
	</div>
</div>
