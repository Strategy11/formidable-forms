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
<div id="frm-onboarding-wizard-page" class="frm_wrap" data-current-step="consent-tracking">
	<div id="frm-onboarding-container" class="frm-flex-col frm-justify-center frm-items-center">
		<ul id="frm-onboarding-rootline" class="frm-rootline">
			<li class="frm-rootline-item" data-step="consent-tracking">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon frm_svg9', array( 'aria-hidden' => 'true' ) ); ?>
			</li>
			<li class="frm-rootline-item" data-step="install-addons">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon frm_svg9', array( 'aria-hidden' => 'true' ) ); ?>
			</li>
			<li class="frm-rootline-item" data-step="success">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon frm_svg9', array( 'aria-hidden' => 'true' ) ); ?>
			</li>
		</ul>

		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file;
		}
		?>

		<a id="frm-onboarding-return-dashboard" href="<?php echo esc_url( admin_url( 'admin.php?page=' . FrmDashboardController::PAGE_SLUG ) ); ?>">
			<?php esc_html_e( 'Exit Onboarding', 'formidable' ); ?>
		</a>
	</div>

	<?php if ( $license_key ) { ?>
		<input type="hidden" id="frm-license-key" name="frm-license-key" value="<?php echo esc_attr( $license_key ); ?>">
	<?php } ?>
</div>
