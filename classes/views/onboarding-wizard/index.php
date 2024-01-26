<?php
/**
 * Onboarding Wizard Page.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-onboarding-wizard-page" class="frm_wrap">
	<div class="frm-onboarding-bg frm-fadein-up-back">
		<img class="frm-w-full" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/onboarding-wizard/bg.png" alt="<?php esc_attr_e( 'Background Image', 'formidable' ); ?>" />
	</div>

	<div class="frm-onboarding-container frm-flex-box frm-justify-center frm-items-center">
		<section id="frm-onboarding-welcome-step" class="frm-card-box frmcenter frm-fadein-up" data-step="1" data-step-name="welcome">
			<div class="frm-card-box-header frm-flex frm-justify-center">
				<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.png" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard', 'formidable' ); ?>" />
			</div>

			<div class="frm-card-box-content frm-mt-md">
				<h2 class="frm-card-box-title frm-mb-sm"><?php esc_html_e( 'Welcome to Formidable Forms', 'formidable' ); ?></h2>
				<p class="frm-card-box-text"><?php esc_html_e( 'This quick setup wizard will help you configure the basic settings and get you started in no more than 2 minutes.', 'formidable' ); ?></p>
			</div>

			<div class="frm-card-box-footer frm-justify-center">
				<a href="#" class="button button-secondary frm-button-secondary" role="button">
					<?php esc_html_e( 'Proceed without Account', 'formidable' ); ?>
				</a>
				<a href="#" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
					<?php esc_html_e( 'Connect Account', 'formidable' ); ?>
				</a>
			</div>
		</section>
	</div>
</div>
