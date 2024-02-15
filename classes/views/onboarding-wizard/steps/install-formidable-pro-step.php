<?php
/**
 * Onboarding Wizard - Install Formidable PRO Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-install-formidable-pro-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frmcenter frm_hidden" data-step-name="install-formidable-pro">
	<span class="frm-card-box-progress-bar"><span></span></span>

	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_key_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content">
		<h2 class="frm-card-box-title"><?php esc_html_e( 'Install Formidable PRO', 'formidable' ); ?></h2>
		<p class="frm-card-box-text"><?php esc_html_e( 'Formidable Forms needs to install the Formidable Forms Pro plugin on your website, so you can access the premium features.', 'formidable' ); ?></p>

		<div class="frm-box frm-flex frm-justify-between frm-items-center frm-mt-md frm-mb-xs">
			<span class="frm-text-grey-900 frm-font-medium"><?php esc_html_e( '1. Download PRO', 'formidable' ); ?></span>
			<a class="frm-link-with-external-icon" href="#" target="_blank">
				<span class="frm-font-semibold"><?php esc_html_e( 'Download', 'formidable' ); ?></span>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_up_right' ); ?>
			</a>
		</div>

		<div class="frm-box frm-flex frm-justify-between frm-items-center">
			<span class="frm-text-grey-900 frm-font-medium"><?php esc_html_e( '2. Install on your website', 'formidable' ); ?></span>
			<a class="frm-link-with-external-icon" href="#" target="_blank">
				<span class="frm-font-semibold"><?php esc_html_e( 'Upload Plugin', 'formidable' ); ?></span>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrow_up_right' ); ?>
			</a>
		</div>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-install-formidable-pro-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Continue', 'formidable' ); ?>
		</a>
	</div>
</section>
