<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<div class="frm_grid_container frm_no_grid_750 frm-mb-lg">
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
				<p><?php esc_html_e( 'Thanks for choosing Formidable Forms - The most powerful and versatile form builder for WordPress', 'formidable' ); ?></p>
				<?php FrmWelcomeController::maybe_show_license_box(); ?>

			</div>
		</div>
		<div class="frm6">
			<div class="frm-player frm-text-right">
				<iframe width="480" height="240" src="https://www.youtube.com/embed/7X2BqhRsXcg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<div class="frm_grid_container frm-mb-lg">
		<div class="frm6 frm-ws-block frm-create-blank-form">
			<div class="frm-icon-wrapper">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
			</div>
			<div class="frm-fill">
				<h3><?php esc_html_e( 'New Blank Form', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Create a new view from scratch', 'formidable' ); ?></p>
			</div>
			<span class="caret rotate-270"></span>
		</div>
		<div class="frm6 frm-ws-block frm-trigger-new-form-modal">
			<div class="frm-icon-wrapper frm-icon-briefcase">
				<svg class="frmsvg" viewBox="0 0 24 24"><path d="M15 6.5a1 1 0 01-1-1V4h-4v1.5a1 1 0 01-2 0V4c0-1.1.9-2 2-2h4a2 2 0 012 2v1.5a1 1 0 01-1 1z" fill="currentColor" fill-opacity=".6"/><path d="M18 12.5v1c0 .3-.3.7-.8.7s-.7-.4-.7-.8v-.9h-9v1c0 .3-.3.7-.8.7s-.7-.4-.7-.8v-.9H0v6.8A2.8 2.8 0 002.8 22h18.4a2.8 2.8 0 002.8-2.8v-6.7h-6zM21.3 5H2.6A2.8 2.8 0 000 7.8V11h6V9.7c0-.4.3-.7.8-.7s.7.3.7.8V11h9V9.7c0-.4.3-.7.8-.7s.7.3.7.8V11h6V7.7A2.8 2.8 0 0021.2 5z" fill="currentColor"/></svg>
			</div>
			<div class="frm-fill">
				<h3><?php esc_html_e( 'New Form From a Template', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Check out our powerful pre-built templates', 'formidable' ); ?></p>
			</div>
			<span class="caret rotate-270"></span>
		</div>
	</div>

	<div class="frm-ws-carousel-wrapper">
		<h2><?php esc_html_e( 'Get limitless possibilities', 'formidable' ); ?></h2>

		<div class="frm_grid_container">
			<div class="frm3">
				<div class="frm-icon-wrapper">
					<svg class="frmsvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 65 65"><path d="M57 39.5V11a4 4 0 00-4-4H11a4 4 0 00-4 4v42a4 4 0 004 4h42a4 4 0 004-4v-3M32.5 8.5v48" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/><path d="M40 28h10.5M40 34h10.5" stroke="currentColor" opacity=".6" stroke-width="3" stroke-linecap="round" fill="none"/><path d="M32 33H8m12.5-18.5V25m3.6 16.2l-7.4 7.4M15 20h10.5m-9.1 21.2l7.4 7.4" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/></svg>
				</div>
				<h3><?php esc_html_e( 'Calculators', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Give instant online estimates and calculate advanced product options.', 'formidable' ); ?></p>
				<span><?php FrmWelcomeController::upgrade_to_pro_button(); ?></span>
			</div>
			<div class="frm3">
				<div class="frm-icon-wrapper">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_comment_icon' ); ?>
				</div>
				<h3><?php esc_html_e( 'Survey and polls', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Collect customer feedback & data like a pro - no coding required.', 'formidable' ); ?></p>
				<span><?php FrmWelcomeController::upgrade_to_pro_button(); ?></span>
			</div>
			<div class="frm3">
				<div class="frm-icon-wrapper">
					<svg class="frmsvg" viewBox="0 0 28 28"><path d="M25 12.9L14.3 23.6a7 7 0 11-10-9.9L15.2 3a4.7 4.7 0 016.6 6.6L11 20.3A2.3 2.3 0 017.7 17l9.9-9.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
				</div>
				<h3><?php esc_html_e( 'File uploads', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Easily upload documents, files, photos, and music for user avatars, featured images, or email attachments.', 'formidable' ); ?></p>
				<span><?php FrmWelcomeController::upgrade_to_pro_button(); ?></span>
			</div>
			<div class="frm3">
				<div class="frm-icon-wrapper">
					<svg class="frmsvg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.3 5.5H1.8C1 5.5.5 4.9.5 4.3V1.8C.5 1 1.1.5 1.8.5h5.4c.7 0 1.3.6 1.3 1.3v2.4c0 .7-.6 1.3-1.2 1.3zm0 9H1.8c-.7 0-1.3-.6-1.3-1.2v-2.5c0-.7.6-1.3 1.3-1.3h5.4c.7 0 1.3.6 1.3 1.2v2.5c0 .7-.6 1.3-1.2 1.3zm0 9H1.8c-.7 0-1.3-.6-1.3-1.2v-2.5c0-.7.6-1.3 1.3-1.3h5.4c.7 0 1.3.6 1.3 1.2v2.5c0 .7-.6 1.3-1.2 1.3z" stroke="currentColor" opacity=".6"/><path d="M23.3 1.4H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6zm0 2H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6zm0 7H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6zm0 2H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6zm0 7H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6zm0 2H11.7c-.4 0-.7.3-.7.6s.3.6.8.6h11.5c.4 0 .7-.3.7-.6s-.3-.6-.7-.6z" fill="currentColor"/></svg>
				</div>
				<h3><?php esc_html_e( 'Display form data with views', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Now you can display form data in custom Views without any PHP.', 'formidable' ); ?></p>
				<span><?php FrmWelcomeController::maybe_show_conditional_action_button( 'views', 'views-info' ); ?></span>
			</div>
		</div>
	</div>
</div>
