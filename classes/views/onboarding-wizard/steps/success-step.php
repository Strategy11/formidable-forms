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
		<div class="frm-circled-icon frm-circled-icon-large frm-circled-icon-green frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-mt-sm">
		<h2 class="frm-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'formidable' ); ?></h2>
		<p class="frm-card-box-text">
			<?php esc_html_e( 'We\'re thrilled to have you and hope you love your experience with Formidable Forms', 'formidable' ); ?>
		</p>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'footer-class'               => 'frm-justify-center frm-mt-2xl',
			'display-back-button'        => false,
			'primary-button-text'        => esc_html__( 'Go to Dashboard', 'formidable' ),
			'primary-button-href'        => admin_url( 'admin.php?page=' . FrmDashboardController::PAGE_SLUG ),
			'primary-button-role'        => false,
			'secondary-button-text'      => esc_html__( 'Create a Form', 'formidable' ),
			'secondary-button-href'      => admin_url( 'admin.php?page=' . FrmFormTemplatesController::PAGE_SLUG ),
			'secondary-button-role'      => false,
			'secondary-button-skip-step' => false,
		)
	);
	?>
</section>
