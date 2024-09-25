<?php
/**
 * Onboarding Wizard - Default Email Address Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<section id="frm-onboarding-default-email-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frm_hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_email_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Email Setup', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frmcenter">
			<?php esc_html_e( 'Setup the sender address shown to recipients (from) and the default email for admin notifications (to).', 'formidable' ); ?>
		</p>

		<div class="frm_form_field frm-mt-lg">
			<p>
				<label for="frm-onboarding-from-email"><?php esc_html_e( 'From Address', 'formidable' ); ?></label>
				<input type="email" name="frm-onboarding-from-email" id="frm-onboarding-from-email" class="frm-input-field frm-gap-xs" placeholder="<?php esc_attr_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( FrmAppHelper::get_settings()->from_email ); ?>" />
				<?php
				FrmAppHelper::print_setting_error(
					array(
						'id'     => 'frm-onboarding-from-email-error',
						'errors' => array(
							'invalid' => __( 'Email is invalid', 'formidable' ),
							'empty'   => __( 'Email is empty', 'formidable' ),
						),
					)
				);
				?>
			</p>
			<p>
				<label for="frm-onboarding-default-email-field"><?php esc_html_e( 'To Address', 'formidable' ); ?></label>
				<input type="email" name="frm-onboarding-default-email-field" id="frm-onboarding-default-email-field" class="frm-input-field frm-gap-xs" placeholder="<?php esc_attr_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( FrmAppHelper::get_settings()->default_email ); ?>" />
				<!-- Email Error -->
				<?php
				FrmAppHelper::print_setting_error(
					array(
						'id'     => 'frm-onboarding-email-step-error',
						'errors' => array(
							'invalid' => __( 'Email is invalid', 'formidable' ),
							'empty'   => __( 'Email is empty', 'formidable' ),
						),
					)
				);
				?>
			</p>

			<?php if ( ! $pro_is_installed ) { ?>
				<label for="frm-onboarding-subscribe" class="frm-flex frm-gap-xs frm-mb-xs">
					<input type="checkbox" name="frm-onboarding-subscribe" id="frm-onboarding-subscribe" class="frm-mx-0 frm-mt-2xs" checked />
					<span><?php esc_html_e( 'Subscribe to our newsletter and get 20+ free form templates', 'formidable' ); ?></span>
				</label>
			<?php } ?>

			<label for="frm-onboarding-allow-tracking" class="frm-flex frm-gap-xs frm-mb-xs">
				<input type="checkbox" name="frm-onboarding-allow-tracking" id="frm-onboarding-allow-tracking" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Help improve Formidable by sharing anonymous usage data', 'formidable' ); ?></span>
			</label>

			<label for="frm-onboarding-summary-emails" class="frm-flex frm-gap-xs">
				<input type="checkbox" name="frm-onboarding-summary-emails" id="frm-onboarding-summary-emails" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Send me monthly and yearly summary emails with entry and revenue data from my forms', 'formidable' ); ?></span>
			</label>
		</div>

		<?php FrmAppController::api_email_form( 'freetemplates' ); ?>
	</div>

	<?php FrmOnboardingWizardHelper::print_footer( array( 'primary-button-id' => 'frm-onboarding-setup-email-step-button' ) ); ?>
</section>
