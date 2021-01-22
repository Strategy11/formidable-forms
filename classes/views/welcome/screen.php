<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-ws-wrapper">
	<div class="frm-row">
		<div class="frm-column">
			<div class="frm-ws-block-1">
				<div class="frm-ws-logo"><img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/logo.svg' ); ?>" alt="Logo"></div>
				<h1><?php esc_html_e( 'Welcome to Formidable Forms!', 'formidable' ); ?></h1>
				<p><?php esc_html_e( 'Thanks for choosing Formidable Froms - The most powerful and vesatile form builder for Wordpress', 'formidable' ); ?></p>
				<div class="frm-ws-buttons">
					<div class="frm-ws-btn frm-bg-blue" data-location="https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=settings-license&utm_campaign=liteplugin">
						<span><?php esc_html_e( 'Activate Formidable Form', 'formidable' ); ?></span>
					</div>
					<div class="frm-ws-btn frm-bg-transparent" data-location="https://formidableforms.com/api-connect?utm_source=WordPress&utm_medium=connect&utm_campaign=liteplugin&v=2&siteurl=http://strategy11.test&url=http://strategy11.test/wp-json/&token=6200a7febd03e9aa56f15a7a017a3f4eeeae14f5168591a2a60f54fb3c2acdd0466629313a31fd52fff645fc9351928e4ec9c2e3354d29526f65008991d7a4d9&l=0d8e0c8ed0d356a923c9631b437c7df2">
						<span><?php esc_html_e( 'Reactivate my account', 'formidable' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="frm-column">
			<div class="frm-player">
				<iframe width="480" height="240" src="https://www.youtube.com/embed/-eGuL_OWHw4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<div class="second-section">
		<div class="frm-row">
			<div class="frm-column">
				<div class="frm-ws-block frm-create-form">
					<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_ws_plus_icon' ); ?></div>
					<h3><?php esc_html_e( 'New Blank Form', 'formidable' ); ?></h3>
					<p><?php esc_html_e( 'Create a new view from scratch', 'formidable' ); ?></p>
					<div class="frm-right-arrow"><?php FrmAppHelper::icon_by_class( 'frmfont frm_ws_arrow_right_icon' ); ?></div>
				</div>
			</div>
			<div class="frm-column">
				<div class="frm-ws-block frm-trigger-new-form-modal">
					<div class="frm-icon icon-briefcase"><?php FrmAppHelper::icon_by_class( 'frmfont frm_briefcase_icon' ); ?></div>
					<h3><?php esc_html_e( 'New Form From a Template', 'formidable' ); ?></h3>
					<p><?php esc_html_e( 'Check out our powerful pre-built templates', 'formidable' ); ?></p>
					<div class="frm-right-arrow"><?php FrmAppHelper::icon_by_class( 'frmfont frm_ws_arrow_right_icon' ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="frm-ws-carousel-wrapper">
		<h4><?php esc_html_e( 'Unlimit your possibilities', 'formidable' ); ?></h4>
		<div class="frm-arrows">
			<span><img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/arrow-left.svg' ); ?>"></span>
			<span class="frm-right-arrow"><img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/arrow-right.svg' ); ?>"></span>
		</div>
		<div class="frm-row">
			<div class="frm-column frm-col-4">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_ws_calculator_icon' ); ?></div>
				<h3><?php esc_html_e( 'Calculators', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Give instant online estimates and calculate advanced product options.', 'formidable' ); ?></p>
				<div class="frm-btn" data-location="https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=addons&utm_campaign=liteplugin&utm_content=activecampaign-wordpress-plugin"><span><?php esc_html_e( 'Upgrade', 'formidable' ); ?></span></div>
			</div>
			<div class="frm-column frm-col-4">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_chat_bubbles_icon' ); ?></div>
				<h3><?php esc_html_e( 'Survey and Polls', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Collect customer feedback & data like a pro - no coding required.', 'formidable' ); ?></p>
				<div class="frm-btn" data-location="https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=addons&utm_campaign=liteplugin&utm_content=activecampaign-wordpress-plugin"><span><?php esc_html_e( 'Upgrade', 'formidable' ); ?></span></div>
			</div>
			<div class="frm-column frm-col-4">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_paperclip_icon' ); ?></div>
				<h3><?php esc_html_e( 'File uploads', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Easily upload documents, files, photos, and music for user avatars, featured images, or email attachments.', 'formidable' ); ?></p>
				<div class="frm-btn" data-location="https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=addons&utm_campaign=liteplugin&utm_content=activecampaign-wordpress-plugin"><span><?php esc_html_e( 'Upgrade', 'formidable' ); ?></span></div>
			</div>
			<div class="frm-column frm-col-4">
				<div class="frm-icon"><?php FrmAppHelper::icon_by_class( 'frmfont frm_listings_icon' ); ?></div>
				<h3><?php esc_html_e( 'Display form data with views', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Now you can display form data in custom Views without any PHP.', 'formidable' ); ?></p>
				<div class="frm-btn" data-location="https://formidableforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=addons&utm_campaign=liteplugin&utm_content=activecampaign-wordpress-plugin"><span><?php esc_html_e( 'Upgrade', 'formidable' ); ?></span></div>
			</div>
		</div>
	</div>
</div>
