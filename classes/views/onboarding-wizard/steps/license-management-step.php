<?php
/**
 * Onboarding Wizard - License Management Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<section id="frm-onboarding-license-management-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frm_hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_key_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'License Management', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frmcenter"><?php esc_html_e( 'To get started, please activate your license by following the simple steps below.', 'formidable' ); ?></p>

		<div class="frm_form_field frm-mt-lg">
			<p>
				<label for="edd_formidable_pro_license_key"><?php esc_html_e( 'License key', 'formidable' ); ?></label>
				<input id="edd_formidable_pro_license_key" name="proplug-license" class="frm-mt-2xs" type="text" placeholder="<?php esc_attr_e( 'Enter your license key', 'formidable' ); ?>" />
			</p>
		</div>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'primary-button-text'   => esc_html__( 'Activate & Continue', 'formidable' ),
			'primary-button-id'     => 'frm-onboarding-save-license-button',
			'primary-button-class'  => 'frm_authorize_link',
			'primary-button-plugin' => 'formidable_pro',
		)
	);
	?>

	<div class="frm_pro_license_msg frm_hidden"></div>
</section>
