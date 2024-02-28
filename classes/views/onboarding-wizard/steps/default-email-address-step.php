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
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Default Email Address', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frmcenter"><?php esc_html_e( 'Subscribe to our mailing list so you know first when we release new features!', 'formidable' ); ?></p>

		<div class="frm_form_field frm-mt-lg">
			<p>
				<label for="frm-onboarding-default-email-field"><?php esc_html_e( 'Default email address', 'formidable' ); ?></label>
				<input type="email" name="frm-onboarding-default-email-field" id="frm-onboarding-default-email-field" class="frm-input-field frm-gap-xs" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
				<!-- Email Error -->
				<span id="frm-onboarding-email-step-error" class="frm-validation-error frm-mt-xs frm_hidden">
					<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
					<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
				</span>
			</p>

			<label for="frm-onboarding-subscribe" class="frm-flex frm-gap-xs frm-mb-xs">
				<input type="checkbox" name="frm-onboarding-subscribe" id="frm-onboarding-subscribe" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Subscribe to our mailing list and get 20+ Free Form Templates', 'formidable' ); ?></span>
			</label>

			<label for="frm-onboarding-allow-tracking" class="frm-flex frm-gap-xs">
				<input type="checkbox" name="frm-onboarding-allow-tracking" id="frm-onboarding-allow-tracking" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Help make Formidable Forms better by anonymously sharing information about your usage', 'formidable' ); ?></span>
			</label>
		</div>

		<?php FrmAppController::api_email_form( 'freetemplates' ); ?>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-setup-email-step-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Next Step', 'formidable' ); ?>
		</a>
	</div>
</section>
