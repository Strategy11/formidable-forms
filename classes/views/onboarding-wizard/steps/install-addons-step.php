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
<section id="frm-onboarding-install-addons-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frm_hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_puzzle_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Install Formidable Add-ons', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frmcenter">
			<?php
			if ( ! $pro_is_installed ) {
				esc_html_e( 'A few of our add-ons come with every installation. But more are available on the add-ons page!', 'formidable' );
			} else {
				esc_html_e( 'Here\'s just a few of our most popular add-ons! You\'ll find even more on the add-ons page.', 'formidable' );
			}
			?>
		</p>

		<div class="frm-mt-md">
			<?php foreach ( $available_addons as $key => $addon ) { ?>
				<label <?php FrmOnboardingWizardHelper::add_addon_label_attributes( $key, $addon ); ?>>
					<span class="frm-flex-center frm-gap-xs">
						<?php echo esc_html( $addon['title'] ); ?>
						<?php if ( ! empty( $addon['help-text'] ) ) { ?>
							<span class="frm_help" data-original-title="<?php echo esc_attr( $addon['help-text'] ); ?>">
								<?php FrmAppHelper::icon_by_class( 'frmfont frm_info_icon' ); ?>
							</span>
						<?php } ?>
					</span>
					<input <?php FrmOnboardingWizardHelper::add_addon_input_attributes( $key, $addon ); ?>/>
				</label>
			<?php } ?>
		</div>

		<div class="frm-cta frm-cta-border frm-cta-green frm-p-sm frm-mt-sm">
			<span class="frm-banner-title frm-font-semibold frm-flex">
				<?php
				printf(
					/* translators: %s: The count of add-ons */
					esc_html__( 'Get access to %s more add-ons', 'formidable' ),
					esc_html( $addons_count )
				);
				?>
			</span>
			<span class="frm-banner-text frm-text-xs">
				<?php
				if ( ! $pro_is_installed ) {
					printf(
						/* translators: %1$s: Open anchor tag, %2$s: Close anchor tag */
						esc_html__( '%1$sUpgrade to PRO%2$s and get more out of Formidable Forms', 'formidable' ),
						'<a href="' . esc_url( $upgrade_link ) . '" target="_blank">',
						'</a>'
					);
				} else {
					printf(
						/* translators: %1$s: Open anchor tag, %2$s: Close anchor tag */
						esc_html__( 'Check them all out on the %1$sadd-ons%2$s page.', 'formidable' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-addons' ) ) . '" target="_blank">',
						'</a>'
					);
				}
				?>
			</span>
		</div>
	</div>

	<?php
	FrmOnboardingWizardHelper::print_footer(
		array(
			'primary-button-text' => esc_html__( 'Install & Finish Setup', 'formidable' ),
			'primary-button-id'   => 'frm-onboarding-install-addons-button',
		)
	);
	?>
</section>
