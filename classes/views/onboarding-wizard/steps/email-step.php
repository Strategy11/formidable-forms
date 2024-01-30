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

<section id="frm-onboarding-email-step" class="frm-card-box frm-has-progress-bar frm_hidden" data-step-name="email">
	<span class="frm-card-box-progress-bar">
		<span data-step="1" data-total-step="3"></span>
	</span>

	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_email_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Default Email Address', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frm-px-lg frmcenter"><?php esc_html_e( 'Subscribe to our mailing list so you know first when we release new features!', 'formidable' ); ?></p>

		<div class="frm_form_field frm-mt-lg">
			<p>
				<label for="frm-default-email-field"><?php esc_html_e( 'Default email address', 'formidable' ); ?></label>
				<input id="frm-default-email-field" class="frm-input-field frm-gap-xs" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</p>

			<label for="frm-subscribe-free-templates" class="frm-flex frm-gap-xs frm-mb-xs">
				<input type="checkbox" name="frm-subscribe-free-templates" id="frm-subscribe-free-templates" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Subscribe to our mailing list and get 20+ Free Form Templates', 'formidable' ); ?></span>
			</label>

			<label for="frm-allow-tracking" class="frm-flex frm-gap-xs">
				<input type="checkbox" name="frm-allow-tracking" id="frm-allow-tracking" class="frm-mx-0 frm-mt-2xs" checked />
				<span><?php esc_html_e( 'Help make Formidable Forms better by anonymously sharing information about your usage', 'formidable' ); ?></span>
			</label>

			<span id="frm-default-email-field-error" class="frm_hidden">
				<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
				<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
			</span>
		</div>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-get-email-btn" class="button button-primary frm-button-primary frm-onboarding-next-step" role="button">
			<?php esc_html_e( 'Next Step', 'formidable' ); ?>
		</a>
	</div>
</section>
