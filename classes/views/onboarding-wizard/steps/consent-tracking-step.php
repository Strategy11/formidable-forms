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
			'primary-button-text'      => __( 'Allow & Continue', 'formidable' ),
			'primary-button-id'        => 'frm-onboarding-consent-tracking',
			'primary-button-with-icon' => true,
			'secondary-button-text'    => __( 'Skip', 'formidable' ),
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
			<div class="frm-flex frm-gap-xs frm-items-center frm-py-sm">
				<span><?php FrmAppHelper::icon_by_class( 'frmfont frm_user_icon frm_svg15', array( 'aria-hidden' => 'true' ) ); ?></span>

				<div class="frm-flex-col frm-gap-2xs frm-ml-2xs">
					<h4 class="frm-flex frm-gap-xs frm-items-center frm-text-sm frm-font-medium frm-text-grey-700 frm-m-0">
						<?php
						esc_html_e( 'View Basic Profile Info', 'formidable' );

						FrmAppHelper::tooltip_icon(
							__( 'Never miss important updates, get security warnings before they become public knowledge, and receive notifications about special offers and awesome new features.', 'formidable' ),
							array(
								'class' => 'frm-inline-flex',
							)
						);
						?>
					</h4>
					<span class="frm-text-xs frm-text-grey-500 frm-m-0"><?php esc_html_e( 'Your WordPress user’s: first & last name and email address', 'formidable' ); ?></span>
				</div>
			</div>

			<div class="frm-flex frm-gap-xs frm-items-center frm-py-sm">
				<span><?php FrmAppHelper::icon_by_class( 'frmfont frm_sample_form_icon frm_svg15', array( 'aria-hidden' => 'true' ) ); ?></span>

				<div class="frm-flex-col frm-gap-2xs frm-ml-2xs">
					<h4 class="frm-flex frm-gap-xs frm-items-center frm-text-sm frm-font-medium frm-text-grey-700 frm-m-0">
						<?php
						esc_html_e( 'View Basic Website Info', 'formidable' );

						FrmAppHelper::tooltip_icon(
							__( 'To provide additional functionality that’s relevant to your website, avoid WordPress or PHP incompatibilities that can break your website, and recognize which languages & regions the plugin should be translated and tailored to.', 'formidable' ),
							array(
								'class' => 'frm-inline-flex',
							)
						);
						?>
					</h4>
					<span class="frm-text-xs frm-text-grey-500 frm-m-0"><?php esc_html_e( 'Homepage URL & title, WP & PHP versions, site language', 'formidable' ); ?></span>
				</div>
			</div>

			<div class="frm-flex frm-gap-xs frm-items-center frm-py-sm">
				<span><?php FrmAppHelper::icon_by_class( 'frmfont frm_puzzle_icon_thin frm_svg15', array( 'aria-hidden' => 'true' ) ); ?></span>

				<div class="frm-flex-col frm-gap-2xs frm-ml-2xs">
					<h4 class="frm-flex frm-gap-xs frm-items-center frm-text-sm frm-font-medium frm-text-grey-700 frm-m-0">
						<?php esc_html_e( 'View Basic Plugin Info', 'formidable' ); ?>
					</h4>
					<span class="frm-text-xs frm-text-grey-500 frm-m-0"><?php esc_html_e( 'Current plugin & SDK versions, and if active or uninstalled', 'formidable' ); ?></span>
				</div>
			</div>

			<div class="frm-flex frm-gap-xs frm-items-center frm-py-sm">
				<span><?php FrmAppHelper::icon_by_class( 'frmfont frm-field-colors-style frm_svg20', array( 'aria-hidden' => 'true' ) ); ?></span>

				<div class="frm-flex-col frm-gap-2xs frm-ml-2xs">
					<h4 class="frm-flex frm-gap-xs frm-items-center frm-text-sm frm-font-medium frm-text-grey-700 frm-m-0">
						<?php
						esc_html_e( 'View Plugins & Themes List', 'formidable' );

						FrmAppHelper::tooltip_icon(
							__( 'To ensure compatibility and avoid conflicts with your installed plugins and themes.', 'formidable' ),
							array(
								'class' => 'frm-inline-flex',
							)
						);
						?>
					</h4>
					<span class="frm-text-xs frm-text-grey-500 frm-m-0"><?php esc_html_e( 'Names, slugs, versions, and if active or not', 'formidable' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>
