<?php
/**
 * Onboarding Wizard - Welcome Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-welcome-step" class="frm-onboarding-step frm-card-box frmcenter frm-current" data-step-name="welcome">
	<div class="frm-card-box-header">
		<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.png" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard Logo', 'formidable' ); ?>" />
	</div>

	<div class="frm-card-box-content frm-mt-md">
		<h2 class="frm-card-box-title frm-mb-sm"><?php esc_html_e( 'Welcome to Formidable Forms', 'formidable' ); ?></h2>
		<p class="frm-card-box-text"><?php esc_html_e( 'This quick setup wizard will help you configure the basic settings and get you started in 2 minutes.', 'formidable' ); ?></p>
	</div>

	<div class="frm-card-box-footer frm-justify-center">
		<a href="#" id="frm-onboarding-proceed-without-account" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Proceed without Account', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-connect-account" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Connect Account', 'formidable' ); ?>
		</a>
	</div>
</section>
