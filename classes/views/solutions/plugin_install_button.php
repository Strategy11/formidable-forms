<?php
/**
 * Solutions plugin install button view
 *
 * @since x.x
 *
 * @var array $step Step information including button_class and button_label
 * @var array $rel  Relationship attributes for the link
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<a rel="<?php echo esc_attr( implode( ',', $rel ) ); ?>" class="<?php echo esc_attr( $step['button_class'] ); ?>">
	<?php echo esc_html( $step['button_label'] ); ?>
</a>
