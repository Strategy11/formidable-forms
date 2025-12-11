<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmPayPalLiteConnectHelper::render_settings_container();

if ( is_callable( array( 'FrmPaymentSettingsController', 'route' ) ) ) {
	?>
	<hr style="margin: var(--gap-lg) 0;">
	<div>
		<?php FrmPaymentSettingsController::route(); ?>
	</div>
	<?php
}
