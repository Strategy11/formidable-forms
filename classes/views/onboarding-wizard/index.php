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
<div id="frm-onboarding-wizard-page" class="frm_wrap">
	<div class="frm-onboarding-bg frm-fadein-up-back">
		<img class="frm-w-full" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/onboarding-wizard/bg.png" alt="<?php esc_attr_e( 'Background Image', 'formidable' ); ?>" />
	</div>

	<div class="frm-onboarding-container frm-flex-box frm-justify-center frm-items-center">
		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file;
		}
		?>
	</div>
</div>
