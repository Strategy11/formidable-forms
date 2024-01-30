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
	<div id="frm-onboarding-bg">
		<?php require $view_path . 'onboarding-svg-bg.php'; ?>
	</div>

	<div class="frm-onboarding-container frm-flex-box frm-justify-center frm-items-center">
		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file;
		}
		?>
	</div>
</div>
