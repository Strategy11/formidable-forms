<?php
/**
 * Admin messages view
 *
 * @since x.x
 *
 * @var array $message Array of admin messages to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php foreach ( $message as $m ) : ?>
	<div class="frm-banner-alert frm_error_style frm_previous_install">
		<?php echo esc_html( $m ); ?>
	</div>
<?php endforeach; ?>
