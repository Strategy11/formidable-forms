<?php
/**
 * Admin messages view
 *
 * @since 4.0.02
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array $message Array of admin messages to display
 */
?>
<?php foreach ( $message as $m ) : ?>
	<div class="frm-banner-alert frm_error_style frm_previous_install">
		<?php echo esc_html( $m ); ?>
	</div>
<?php endforeach; ?>
