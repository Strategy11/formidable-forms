<?php
/**
 * Onboarding Wizard - Install Formidable Add-ons Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-install-addons-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar " data-step-name="install-addons">
	<span class="frm-card-box-progress-bar"><span></span></span>

	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_puzzle_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Install Formidable Add-ons', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frmcenter"><?php esc_html_e( 'A list with add-ons we think you will love. All of our add-ons can be installed from the Add-ons page.', 'formidable' ); ?></p>

		<div class="frm-mt-md">
			<?php foreach ( $available_addons as $key => $addon ) { ?>
				<label <?php FrmOnboardingWizardHelper::add_addon_label_attributes( $key, $addon ); ?>>
					<span><?php echo esc_html( $addon['title'] ); ?></span>
					<input <?php FrmOnboardingWizardHelper::add_addon_input_attributes( $key, $addon ); ?>/>
				</label>
			<?php } ?>

			<label for="frm-onboarding-smtp-addon" class="frm-option-box" data-plugin-slug="gravity-forms-migrator">
				<span><?php esc_html_e( 'Gravity Forms Migrator', 'formidable' ); ?></span>
				<input type="checkbox" name="frm-onboarding-smtp-addon" id="frm-onboarding-smtp-addon" checked />
			</label>
		</div>

		<div class="frm-cta frm-cta-border frm-cta-green frm-p-sm frm-mt-sm">
			<span class="frm-banner-title frm-font-semibold frm-flex">
				<?php esc_html_e( 'Get access to 40 more add-ons', 'formidable' ); ?>
			</span>
			<span class="frm-banner-text frm-text-xs">
				<?php
				printf(
					/* translators: %1$s: Open anchor tag, %2$s: Close anchor tag */
					esc_html__( '%1$sUpgrade to PRO%2$s and get more out of Formidable Forms', 'formidable' ),
					'<a href="' . esc_url( $upgrade_link ) . '" target="_blank">',
					'</a>'
				);
				?>
			</span>
		</div>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-install-addons-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Install & Finish setup', 'formidable' ); ?>
		</a>
	</div>
</section>
