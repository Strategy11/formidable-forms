<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmWelcomeScreenController {

	public static function activation_redirect() {
		$activation_redirect_option = 'frm_welcome_screen_activation_redirect';
		if ( get_option( $activation_redirect_option ) != 'yes' ) {
			return;
		}
		update_option( $activation_redirect_option, 'no' );
		wp_safe_redirect( add_query_arg( array( 'page' => 'formidable-welcome-screen' ), admin_url( 'admin.php' ) ) );
	}

	public static function screen_page() {
		add_submenu_page( 'formidable', 'Welcome Screen', 'Welcome Screen', 'read', 'formidable-welcome-screen', __CLASS__ . '::screen_content' );
	}

	public static function screen_content() {
		?>
			<div class="wrap">
				<h2>Welcome Screen</h2>
			</div>
		<?php
	}

	public static function remove_menu() {
		remove_submenu_page( 'formidable', 'formidable-welcome-screen' );
	}

}
