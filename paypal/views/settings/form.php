<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmPayPalLiteConnectHelper::render_settings_container();

// When the PayPal add-on is active, we want to show the add-on settings here.
if ( is_callable( array( 'FrmPaymentSettingsController', 'route' ) ) ) {
	?>
	<hr style="margin: var(--gap-lg) 0;">
	<h3><?php _e( 'PayPal Standard (Legacy) Settings', 'formidable' ); ?></h3>
	<div>
		<?php FrmPaymentSettingsController::route(); ?>
	</div>
	<?php
}
