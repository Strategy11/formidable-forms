<?php
/**
 * Solutions page links view
 *
 * @since 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array $step Step information including button_class and button_label
 */
?>
<a href="#" target="_blank" rel="noopener" id="frm-redirect-link" class="<?php echo esc_attr( $step['button_class'] ); ?>">
	<?php echo esc_html( $step['button_label'] ); ?>
</a>
