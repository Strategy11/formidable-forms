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

<section id="frm-onboarding-welcome-step" class="frm-card-box frmcenter frm-fadein-up" data-step-name="welcome" data-current-step>
	<div class="frm-card-box-header">
		<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.png" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard', 'formidable' ); ?>" />
	</div>

	<div class="frm-card-box-content frm-mt-md">
		<h2 class="frm-card-box-title frm-mb-sm"><?php esc_html_e( 'Welcome to Formidable Forms', 'formidable' ); ?></h2>
		<p class="frm-card-box-text"><?php esc_html_e( 'This quick setup wizard will help you configure the basic settings and get you started in no more than 2 minutes.', 'formidable' ); ?></p>
	</div>

	<div class="frm-card-box-footer frm-justify-center">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Proceed without Account', 'formidable' ); ?>
		</a>
		<a href="#" class="button button-primary frm-button-primary frm-onboarding-next-step" target="_blank" rel="noopener">
			<?php esc_html_e( 'Connect Account', 'formidable' ); ?>
		</a>
	</div>
</section>
