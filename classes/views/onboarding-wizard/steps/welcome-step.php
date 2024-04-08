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
<section id="frm-onboarding-welcome-step" class="frm-onboarding-step frm-card-box frmcenter frm-current" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.png" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard Logo', 'formidable' ); ?>" />
	</div>

	<div class="frm-card-box-content frm-mt-md">
		<h2 class="frm-card-box-title frm-mb-sm"><?php esc_html_e( 'Welcome to Formidable Forms!', 'formidable' ); ?></h2>
		<p class="frm-card-box-text">
			<?php esc_html_e( 'This quick setup wizard will guide you through the basic settings and get you started in 2 minutes.', 'formidable' ); ?>
		</p>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'footer-class'          => 'frm-justify-center',
			'display-back-button'   => false,
			'primary-button-text'   => esc_html__( 'Connect Account', 'formidable' ),
			'primary-button-id'     => 'frm-onboarding-connect-account',
			'primary-button-href'   => FrmAddonsController::connect_link(),
			'primary-button-role'   => false,
			'secondary-button-text' => esc_html__( 'Proceed without Account', 'formidable' ),
		)
	);
	?>
</section>
