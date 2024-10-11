<?php
/**
 * Onboarding Wizard - Never miss an important update step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<section id="frm-onboarding-consent-tracking-step" class="frm-onboarding-step frm-card-box frmcenter frm-current" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.svg" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard Logo', 'formidable' ); ?>" />
	</div>

	<div class="frm-card-box-content frm-mt-md">
		<h2 class="frm-card-box-title frm-mb-sm"><?php esc_html_e( 'Never miss an important update', 'formidable' ); ?></h2>
		<p class="frm-card-box-text">
			<?php esc_html_e( 'Get key updates, tips, and occasional offers to enhance your WordPress experience. Opt in and help us improve compatibility with your site!', 'formidable' ); ?>
		</p>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'primary-button-text'      => esc_html__( 'Allow & Continue', 'formidable' ),
			'primary-button-id'        => 'frm-onboarding-consent-tracking',
			'primary-button-with-icon' => true,
			'secondary-button-text'    => esc_html__( 'Skip', 'formidable' ),
			'footer-class'             => 'frm-justify-center',
			'display-back-button'      => false,
		)
	);
	?>
</section>
