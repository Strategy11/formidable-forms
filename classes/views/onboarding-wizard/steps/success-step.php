<?php
/**
 * Onboarding Wizard - Success (You're All Set!) Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<section id="frm-onboarding-success-step" class="frm-onboarding-step frm-card-box frmcenter frm_hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<img class="frm-onboarding-logo" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/logo.svg" alt="<?php esc_attr_e( 'Formidable Onboarding Wizard Logo', 'formidable' ); ?>" />
	</div>

	<div class="frm-card-box-content frm-mt-sm">
		<h2 class="frm-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'formidable' ); ?></h2>
		<p class="frm-card-box-text">
			<?php esc_html_e( 'Congratulations on completing the onboarding process! We hope you enjoy using Formidable Forms.', 'formidable' ); ?>
		</p>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'footer-class'               => 'frm-justify-center frm-mt-2xl',
			'display-back-button'        => false,
			'primary-button-text'        => __( 'Go to Dashboard', 'formidable' ),
			'primary-button-href'        => admin_url( 'admin.php?page=' . FrmDashboardController::PAGE_SLUG ),
			'primary-button-role'        => false,
			'secondary-button-text'      => __( 'Create a Form', 'formidable' ),
			'secondary-button-href'      => admin_url( 'admin.php?page=' . FrmFormTemplatesController::PAGE_SLUG ),
			'secondary-button-role'      => false,
			'secondary-button-skip-step' => false,
		)
	);
	?>
</section>
