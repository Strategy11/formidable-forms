<?php
/**
 * Solutions app install disabled button view
 *
 * @since x.x
 *
 * @var array $step Step information including button_class and button_label
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<a href="#" class="<?php echo esc_attr( $step['button_class'] ); ?>">
	<?php echo esc_html( $step['button_label'] ); ?>
</a>
