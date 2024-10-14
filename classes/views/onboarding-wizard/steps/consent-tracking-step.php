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

	<div class="dropdown frm-mt-lg">
		<div id="frm-onboarding-consent-tracking-list" class="frm-dropdown-toggle frm-cursor-pointer" data-toggle="dropdown">
			<span class="frm_bstooltip" data-placement="right">
				<?php esc_html_e( 'Allow Formidable Forms to', 'formidable' ); ?>
			</span>

			<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon frm_svg13', array( 'aria-hidden' => 'true' ) ); ?>
		</div>

		<div class="frm-dropdown-menu frm-mt-sm" aria-labelledby="frm-onboarding-consent-tracking-list">
			<div class="frm-flex frm-gap-xs frm-items-center">
				<span><?php FrmAppHelper::icon_by_class( 'frmfont frm_user_icon', array( 'aria-hidden' => 'true' ) ); ?></span>

				<div class="frm-flex-col frm-gap-2xs frm-ml-2xs">
					<h4 class="frm-text-sm frm-font-medium frm-text-grey-700 frm-m-0"><?php esc_html_e( 'View Basic Profile Info', 'formidable' ); ?></h4>
					<span class="frm-text-xs frm-text-grey-500 frm-m-0">Your WordPress user’s: first & last name and email address</span>
				</div>
			</div>
		</div>
	</div>
</section>
