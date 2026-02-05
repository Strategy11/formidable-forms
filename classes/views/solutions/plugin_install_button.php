<?php
/**
 * Solutions plugin install button view
 *
 * @since 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array $step Step information including button_class and button_label
 * @var array $rel  Relationship attributes for the link
 */
?>
<a rel="<?php echo esc_attr( implode( ',', $rel ) ); ?>" class="<?php echo esc_attr( $step['button_class'] ); ?>">
	<?php echo esc_html( $step['button_label'] ); ?>
</a>
