<?php
/**
 * Onboarding Wizard Page.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-onboarding-wizard-page" class="frm_wrap" data-current-step="welcome">
	<div id="frm-onboarding-bg">
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/onboarding-wizard/onboarding-bg.svg' ); ?>" alt="<?php esc_attr_e( 'Onboarding Wizard Background', 'formidable' ); ?>">
	</div>

	<div id="frm-onboarding-container" class="frm-flex-col frm-justify-center frm-items-center">
		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file;
		}
		?>

		<a id="frm-onboarding-return-dashboard" href="<?php echo esc_url( admin_url( 'admin.php?page=' . FrmDashboardController::PAGE_SLUG ) ); ?>">
			<?php esc_html_e( 'Go to dashboard', 'formidable' ); ?>
		</a>
	</div>

	<?php if ( $license_key ) { ?>
		<input type="hidden" id="frm-license-key" name="frm-license-key" value="<?php echo esc_attr( $license_key ); ?>">
	<?php } ?>
</div>
