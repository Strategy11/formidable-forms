<?php
/**
 * Onboarding Wizard - Install Formidable Pro Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-install-formidable-pro-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frmcenter frm_hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_download_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content">
		<h2 class="frm-card-box-title"><?php esc_html_e( 'Install Formidable Pro', 'formidable' ); ?></h2>
		<p class="frm-card-box-text"><?php esc_html_e( 'It’s time to install Formidable Forms Pro, to get access to the premium features.', 'formidable' ); ?></p>

		<div class="frm-box frm-flex frm-justify-between frm-items-center frm-mt-md frm-mb-xs">
			<span class="frm-text-grey-900 frm-font-medium"><?php esc_html_e( '1. Download PRO', 'formidable' ); ?></span>
			<a href="<?php echo esc_url( 'https://formidableforms.com/account/?utm_source=WordPress&utm_medium=onboarding-wizard&utm_campaign=liteplugin&utm_content=download-pro' ); ?>" class="frm-link-with-external-icon" target="_blank">
				<span class="frm-font-semibold"><?php esc_html_e( 'Download', 'formidable' ); ?></span>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_up_right' ); ?>
			</a>
		</div>

		<div class="frm-box frm-flex frm-justify-between frm-items-center">
			<span class="frm-text-grey-900 frm-font-medium"><?php esc_html_e( '2. Install on your website', 'formidable' ); ?></span>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php' ) ); ?>" class="frm-link-with-external-icon" target="_blank">
				<span class="frm-font-semibold"><?php esc_html_e( 'Upload Plugin', 'formidable' ); ?></span>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_up_right' ); ?>
			</a>
		</div>

		<!-- Error Handleing -->
		<span id="frm-onboarding-check-pro-installation-error" class="frm-validation-error frm-mt-xs frm_hidden">
			<span><?php esc_html_e( 'Formidable Pro is currently inactive!', 'formidable' ); ?></span>
		</span>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-check-pro-installation-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Continue', 'formidable' ); ?>
		</a>
	</div>
</section>
