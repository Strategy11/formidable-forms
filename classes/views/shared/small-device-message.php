<?php
/**
 * Small device message.
 *
 * @since 6.20
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! get_user_option( 'frm_ignore_small_screen_warning', get_current_user_id() ) ) {
	?>
<div id="frm_small_device_message_container">
	<div id="frm_small_device_message">
		<svg xmlns="http://www.w3.org/2000/svg" width="32" height="49" fill="none"><rect width="30" height="47" x="1" y="1" fill="#F5FAFF" stroke="#4199FD" stroke-width="2" rx="3"/><rect width="8" height="2" x="12" y="5" fill="#C0DDFE" rx="1"/><path stroke="#80BBFE" stroke-width="1.5" d="M23 33c0-3.314-2.91-6-6.5-6S10 29.686 10 33"/><circle cx="10.5" cy="20.5" r="1" fill="#80BBFE" stroke="#80BBFE"/><circle cx="21.5" cy="20.5" r="1" fill="#80BBFE" stroke="#80BBFE"/></svg>
		<b><?php esc_html_e( 'More on bigger devices', 'formidable' ); ?></b>
		<p><?php esc_html_e( 'For the best experience, we recommend using Formidable Forms on larger devices such as a desktop or tablet.', 'formidable' ); ?></p>
		<div><a href="<?php echo esc_url( admin_url() ); ?>" class="frm-button-primary"><?php esc_html_e( 'Go Back', 'formidable' ); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a id="frm_small_screen_proceed_button" href="#" class="frm-button-secondary"><?php esc_html_e( 'Proceed Anyway', 'formidable' ); ?></a></div>
	</div>
</div>
	<?php
}
